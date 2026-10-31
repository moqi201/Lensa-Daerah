<?php

namespace Tests\Feature;

use App\Models\DetailPuskesmas;
use App\Models\PuskesmasJabar;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * CATATAN: seluruh data di berkas ini adalah FIXTURE UJI buatan sendiri,
 * bukan data resmi. Struktur respons yang dipakai ({"data":[...]} dan nama field)
 * mengikuti asumsi kode asli dan BELUM diverifikasi terhadap API sebenarnya.
 * Tes ini menguji perilaku kode, bukan keakuratan dataset resmi.
 */
class PuskesmasDataTest extends TestCase
{
    private const DETAIL = '*daftar-puskesmas-di-jawa-barat*';
    private const AGG    = '*jumlah-puskesmas-berdasarkan-kabupatenkota*';

    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
        Cache::flush();
    }

    private function rec(array $o = []): array
    {
        return array_merge([
            'kode_puskesmas'      => 'TEST-001',
            'nama_puskesmas'      => 'PUSKESMAS UJI A',
            'nama_kecamatan'      => 'KECAMATAN UJI',
            'nama_kabupaten_kota' => 'KOTA BANDUNG',
            'alamat'              => 'Jl. Uji No. 1',
        ], $o);
    }

    /* ----------------------------- DETAIL: sukses & cache ----------------------------- */

    public function test_success_is_live_then_served_from_cache_without_second_request(): void
    {
        Http::fake([self::DETAIL => Http::response(['data' => [$this->rec()]], 200)]);

        $first  = DetailPuskesmas::getProcessedData();
        $second = DetailPuskesmas::getProcessedData();

        $this->assertSame('LIVE_REST_API', $first['meta']['source']);
        $this->assertSame('CACHE', $second['meta']['source']);
        $this->assertCount(1, $second['items']);
        $this->assertNotNull($second['meta']['fetched_at']);
        Http::assertSentCount(1);
    }

    /* ----------------------------- DETAIL: kegagalan ----------------------------- */

    public function test_http_error_without_cache_is_unavailable_and_has_no_dummy_data(): void
    {
        Http::fake([self::DETAIL => Http::response('server error', 500)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertSame('UNAVAILABLE', $data['meta']['source']);
        $this->assertSame('http_error', $data['meta']['error_code']);
        $this->assertSame('DATA_TIDAK_TERSEDIA', $data['meta']['classification']);
        $this->assertSame([], $data['items']);
        $this->assertStringNotContainsString('CIBINONG', json_encode($data));
        $this->assertFalse(Cache::has(DetailPuskesmas::cacheKey('fresh')), 'Kegagalan tidak boleh disimpan sebagai cache data.');
    }

    public function test_timeout_is_unavailable(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $data = DetailPuskesmas::getProcessedData();

        $this->assertSame('UNAVAILABLE', $data['meta']['source']);
        $this->assertSame('connection_error', $data['meta']['error_code']);
    }

    public function test_invalid_json_structure_is_classified_tidak_valid(): void
    {
        Http::fake([self::DETAIL => Http::response(['foo' => 'bar'], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertSame('UNAVAILABLE', $data['meta']['source']);
        $this->assertSame('invalid_structure', $data['meta']['error_code']);
        $this->assertSame('TIDAK_VALID', $data['meta']['classification']);
    }

    public function test_empty_data_is_not_cached_as_official(): void
    {
        Http::fake([self::DETAIL => Http::response(['data' => []], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertSame('UNAVAILABLE', $data['meta']['source']);
        $this->assertSame('empty_data', $data['meta']['error_code']);
        $this->assertFalse(Cache::has(DetailPuskesmas::cacheKey('fresh')));
        $this->assertFalse(Cache::has(DetailPuskesmas::cacheKey('last_good')));
    }

    public function test_failure_triggers_cooldown_instead_of_hammering_api(): void
    {
        Http::fake([self::DETAIL => Http::response('err', 500)]);

        DetailPuskesmas::getProcessedData();
        DetailPuskesmas::getProcessedData();

        Http::assertSentCount(1);
    }

    public function test_stale_copy_is_used_and_labelled_when_api_fails_after_cache_expiry(): void
    {
        Http::fake([self::DETAIL => Http::sequence()
            ->push(['data' => [$this->rec()]], 200)
            ->push('boom', 500)]);

        DetailPuskesmas::getProcessedData();                      // sukses, mengisi cache
        Cache::forget(DetailPuskesmas::cacheKey('fresh'));        // simulasi cache segar kedaluwarsa

        $data = DetailPuskesmas::getProcessedData();              // API gagal (HTTP 500) -> salinan terakhir

        $this->assertSame('CACHE_STALE', $data['meta']['source']);
        $this->assertCount(1, $data['items']);
        $this->assertSame('http_error', $data['meta']['error_code']);
        $this->assertNotNull($data['meta']['fetched_at']);
        // Kegagalan tidak menimpa salinan terakhir yang valid.
        $this->assertCount(1, Cache::get(DetailPuskesmas::cacheKey('last_good'))['records']);
    }

    /* ----------------------------- DETAIL: validasi ----------------------------- */

    public function test_record_missing_required_field_is_excluded_and_reported(): void
    {
        Http::fake([self::DETAIL => Http::response(['data' => [
            $this->rec(),
            $this->rec(['kode_puskesmas' => 'TEST-002', 'nama_puskesmas' => '']),
        ]], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertCount(1, $data['items']);
        $this->assertSame(1, $data['meta']['quality']['counts']['tidak_valid']);
        $this->assertStringContainsString('dikecualikan', implode(' ', $data['meta']['warnings']));
    }

    public function test_empty_optional_fields_keep_record_and_stay_null_without_invented_values(): void
    {
        Http::fake([self::DETAIL => Http::response(['data' => [
            $this->rec(['alamat' => '', 'nama_kecamatan' => null, 'kode_puskesmas' => '  ']),
        ]], 200)]);

        $item = DetailPuskesmas::getProcessedData()['items'][0];

        $this->assertNull($item['alamat']);
        $this->assertNull($item['nama_kecamatan']);
        $this->assertNull($item['kode_puskesmas']);
        $this->assertStringNotContainsString('Tidak Terdata', json_encode($item));
        $this->assertStringNotContainsString('Jawa+Barat', $item['maps_url']);
    }

    public function test_missing_column_in_all_rows_is_reported(): void
    {
        $row = $this->rec();
        unset($row['alamat']);
        Http::fake([self::DETAIL => Http::response(['data' => [$row]], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertContains('alamat', $data['meta']['quality']['missing_columns']);
    }

    public function test_duplicate_codes_are_flagged_but_not_removed(): void
    {
        Http::fake([self::DETAIL => Http::response(['data' => [
            $this->rec(),
            $this->rec(['nama_puskesmas' => 'PUSKESMAS UJI B']),
        ]], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertCount(2, $data['items']);
        $this->assertSame(1, $data['meta']['quality']['duplicate_code_count']);
    }

    public function test_completeness_unverified_when_source_reports_no_total(): void
    {
        Http::fake([self::DETAIL => Http::response(['data' => [$this->rec()]], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertSame('BELUM_DIVERIFIKASI', $data['meta']['classification']);
        $this->assertSame('BELUM_DIVERIFIKASI', $data['meta']['quality']['completeness']['status']);
    }

    public function test_completeness_verified_when_reported_total_matches(): void
    {
        config(['puskesmas.total_paths' => ['meta.total']]);
        Http::fake([self::DETAIL => Http::response(['data' => [$this->rec()], 'meta' => ['total' => 1]], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertSame('TERVERIFIKASI', $data['meta']['quality']['completeness']['status']);
        $this->assertSame('VALID_LENGKAP', $data['meta']['classification']);
    }

    public function test_verified_total_with_empty_optional_field_is_valid_dengan_field_kosong(): void
    {
        config(['puskesmas.total_paths' => ['meta.total']]);
        Http::fake([self::DETAIL => Http::response(['data' => [$this->rec(['alamat' => ''])], 'meta' => ['total' => 1]], 200)]);

        $this->assertSame('VALID_DENGAN_FIELD_KOSONG', DetailPuskesmas::getProcessedData()['meta']['classification']);
    }

    public function test_total_mismatch_is_flagged_not_valid(): void
    {
        config(['puskesmas.total_paths' => ['meta.total']]);
        Http::fake([self::DETAIL => Http::response(['data' => [$this->rec()], 'meta' => ['total' => 5]], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertSame('TIDAK_SESUAI', $data['meta']['quality']['completeness']['status']);
        $this->assertSame('TIDAK_VALID', $data['meta']['classification']);
    }

    /* ----------------------------- DETAIL: pagination ----------------------------- */

    public function test_pagination_collects_all_pages_when_enabled(): void
    {
        config(['puskesmas.detail.limit' => 2, 'puskesmas.detail.paginate' => true]);
        Http::fake([self::DETAIL => Http::sequence()
            ->push(['data' => [$this->rec(['kode_puskesmas' => 'T1']), $this->rec(['kode_puskesmas' => 'T2'])]], 200)
            ->push(['data' => [$this->rec(['kode_puskesmas' => 'T3'])]], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertCount(3, $data['items']);
        $this->assertSame(2, $data['meta']['quality']['completeness']['pages']);
    }

    public function test_pagination_stops_when_api_ignores_skip_and_repeats_same_page(): void
    {
        config(['puskesmas.detail.limit' => 2, 'puskesmas.detail.paginate' => true]);
        $page = ['data' => [$this->rec(['kode_puskesmas' => 'T1']), $this->rec(['kode_puskesmas' => 'T2'])]];
        Http::fake([self::DETAIL => Http::sequence()->push($page, 200)->push($page, 200)->push($page, 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertCount(2, $data['items']);
        Http::assertSentCount(2);
    }

    public function test_reaching_limit_without_total_is_flagged_as_possibly_truncated(): void
    {
        config(['puskesmas.detail.limit' => 2]);
        Http::fake([self::DETAIL => Http::response(['data' => [
            $this->rec(['kode_puskesmas' => 'T1']), $this->rec(['kode_puskesmas' => 'T2']),
        ]], 200)]);

        $data = DetailPuskesmas::getProcessedData();

        $this->assertStringContainsString('terpotong', $data['meta']['quality']['completeness']['note']);
    }

    /* ----------------------------- DETAIL: filter & pencarian ----------------------------- */

    public function test_kabupaten_filter_and_search_work(): void
    {
        Http::fake([self::DETAIL => Http::response(['data' => [
            $this->rec(['kode_puskesmas' => 'T1', 'nama_puskesmas' => 'PUSKESMAS GARUDA UJI', 'nama_kabupaten_kota' => 'KOTA BANDUNG']),
            $this->rec(['kode_puskesmas' => 'T2', 'nama_puskesmas' => 'PUSKESMAS LAIN', 'nama_kabupaten_kota' => 'KABUPATEN BOGOR', 'alamat' => 'Jl. Contoh']),
        ]], 200)]);

        $byKab = DetailPuskesmas::getProcessedData('kota bandung');
        $this->assertCount(1, $byKab['items']);
        $this->assertSame('PUSKESMAS GARUDA UJI', $byKab['items'][0]['nama_puskesmas']);

        $this->assertCount(2, DetailPuskesmas::getProcessedData('SEMUA')['items']);
        $this->assertCount(1, DetailPuskesmas::getProcessedData(null, 'garuda')['items']);
        $this->assertCount(1, DetailPuskesmas::getProcessedData(null, 'contoh')['items']);   // cari di alamat
        $this->assertContains('KABUPATEN BOGOR', $byKab['meta']['kabupaten_options']);        // opsi dari data penuh
    }

    public function test_search_without_result_is_empty_but_source_still_live_or_cache(): void
    {
        Http::fake([self::DETAIL => Http::response(['data' => [$this->rec()]], 200)]);

        $data = DetailPuskesmas::getProcessedData(null, 'tidak-ada-yang-cocok');

        $this->assertSame([], $data['items']);
        $this->assertNotSame('UNAVAILABLE', $data['meta']['source']);
    }

    /* ----------------------------- PuskesmasJabar (agregat) ----------------------------- */

    public function test_aggregate_has_no_fabricated_statistics_and_uses_latest_year_only(): void
    {
        Http::fake([self::AGG => Http::response(['data' => [
            ['nama_kabupaten_kota' => 'KOTA BANDUNG', 'jumlah_puskesmas' => 10, 'tahun' => 2023],
            ['nama_kabupaten_kota' => 'KOTA BANDUNG', 'jumlah_puskesmas' => 12, 'tahun' => 2024],
            ['nama_kabupaten_kota' => 'KOTA BOGOR',   'jumlah_puskesmas' => 5,  'tahun' => 2024],
        ]], 200)]);

        $data = PuskesmasJabar::getProcessedData();

        $this->assertSame(17, $data['summary']['total_puskesmas']);   // 12 + 5, bukan 27
        $this->assertSame(2024, $data['summary']['tahun']);
        $this->assertCount(2, $data['items']);
        foreach (['total_ispa', 'total_bansos', 'total_stunting'] as $key) {
            $this->assertArrayNotHasKey($key, $data['summary']);
        }
    }

    public function test_aggregate_failure_has_null_totals_and_no_fallback_numbers(): void
    {
        Http::fake([self::AGG => Http::response('err', 503)]);

        $data = PuskesmasJabar::getProcessedData();

        $this->assertSame('UNAVAILABLE', $data['meta']['source']);
        $this->assertNull($data['summary']['total_puskesmas']);
        $this->assertSame([], $data['items']);
    }

    public function test_aggregate_invalid_rows_are_excluded(): void
    {
        Http::fake([self::AGG => Http::response(['data' => [
            ['nama_kabupaten_kota' => 'KOTA BANDUNG', 'jumlah_puskesmas' => 'abc', 'tahun' => 2024],
            ['nama_kabupaten_kota' => 'KOTA BOGOR',   'jumlah_puskesmas' => 5,     'tahun' => 2024],
        ]], 200)]);

        $data = PuskesmasJabar::getProcessedData();

        $this->assertCount(1, $data['items']);
        $this->assertSame(1, $data['meta']['quality']['tidak_valid']);
    }

    /* ----------------------------- Partial status (tanpa layout/controller) ----------------------------- */

    public function test_status_partial_shows_unavailable_message(): void
    {
        $html = view('partials.data-status', ['meta' => [
            'source' => 'UNAVAILABLE', 'error' => 'API mengembalikan HTTP 500.', 'warnings' => [], 'classification' => 'DATA_TIDAK_TERSEDIA',
        ]])->render();

        $this->assertStringContainsString('Data sementara tidak tersedia', $html);
        $this->assertStringContainsString('HTTP 500', $html);
    }

    public function test_status_partial_labels_stale_cache(): void
    {
        $html = view('partials.data-status', ['meta' => [
            'source' => 'CACHE_STALE', 'error' => 'x', 'fetched_at' => '2026-01-01T00:00:00+00:00', 'warnings' => [], 'classification' => 'BELUM_DIVERIFIKASI',
        ]])->render();

        $this->assertStringContainsString('salinan lama', $html);
    }
}