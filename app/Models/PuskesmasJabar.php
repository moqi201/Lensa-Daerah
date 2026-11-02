<?php

namespace App\Models;

use App\Models\Concerns\FetchesOpenData;
use Illuminate\Database\Eloquent\Model;

class PuskesmasJabar extends Model
{
    use FetchesOpenData;

    protected static function cachePrefix(): string
    {
        // Versi v16 agar cache data 2019 lama otomatis terhapus
        return 'lensa_daerah:puskesmas_agregat:v16';
    }

    protected static function requestDataset(): array
    {
        $cfg      = config('puskesmas.aggregate');
        $endpoint = $cfg['endpoint'] ?? 'https://galura.jabarprov.go.id/api/bigdata/jumlah-puskesmas-berdasarkan-kabupatenkota-di-jawa-barat';
        $started  = microtime(true);

        try {
            // Minta 100 data dengan sorting id:desc agar data TAHUN 2025 ditarik duluan
            $response = static::httpClient()->get($endpoint, [
                'per_page' => 100,
                'sort'     => 'id:desc'
            ]);
            $latency  = round((microtime(true) - $started) * 1000, 2);

            if (! $response->successful()) {
                return static::failure('http_error', 'API mengembalikan HTTP ' . $response->status() . '.');
            }

            $json = $response->json();
            $rows = (is_array($json) && isset($json['data']) && is_array($json['data'])) ? $json['data'] : [];

            if (empty($rows)) {
                return static::failure('empty_data', 'API merespons sukses tetapi data kosong.');
            }

            $records = [];
            foreach ($rows as $row) {
                if (! is_array($row)) continue;

                $name   = static::cleanString($row['nama_kabupaten_kota'] ?? null);
                $jumlah = $row['jumlah_puskesmas'] ?? null;

                if ($name !== null && is_numeric($jumlah)) {
                    $records[] = [
                        'nama_kabupaten_kota' => mb_strtoupper($name),
                        'jumlah_puskesmas'    => (int) $jumlah,
                        'tahun'               => is_numeric($row['tahun'] ?? null) ? (int) $row['tahun'] : null,
                    ];
                }
            }

            if (empty($records)) {
                return static::failure('no_valid_records', 'Tidak ada record valid ditemukan pada API.');
            }

            $years = array_values(array_unique(array_filter(array_column($records, 'tahun'))));
            sort($years);

            return [
                'ok'      => true,
                'payload' => [
                    'records'    => $records,
                    'years'      => $years,
                    'fetched_at' => now()->toIso8601String(),
                    'latency'    => $latency,
                    'quality'    => [
                        'received'       => count($rows),
                        'valid'          => count($records),
                        'tidak_valid'    => count($rows) - count($records),
                        'classification' => 'VALID_LENGKAP',
                    ],
                ],
            ];

        } catch (\Throwable $e) {
            return static::fromException($e);
        }
    }

    public static function getFallbackRecords(): array
    {
         $file = database_path('data/puskesmas.json');
    if (is_file($file)) {
        $rows = json_decode(file_get_contents($file), true);
        if (is_array($rows) && $rows !== []) {
            return $rows;
        }
    }
        return [
            ['nama_kabupaten_kota' => 'KABUPATEN BOGOR', 'jumlah_puskesmas' => 101, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN SUKABUMI', 'jumlah_puskesmas' => 58, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN CIANJUR', 'jumlah_puskesmas' => 47, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN BANDUNG', 'jumlah_puskesmas' => 62, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN GARUT', 'jumlah_puskesmas' => 67, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN TASIKMALAYA', 'jumlah_puskesmas' => 40, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN CIAMIS', 'jumlah_puskesmas' => 37, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN KUNINGAN', 'jumlah_puskesmas' => 37, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN CIREBON', 'jumlah_puskesmas' => 60, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN MAJALENGKA', 'jumlah_puskesmas' => 32, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN SUMEDANG', 'jumlah_puskesmas' => 35, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN INDRAMAYU', 'jumlah_puskesmas' => 49, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN SUBANG', 'jumlah_puskesmas' => 40, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN PURWAKARTA', 'jumlah_puskesmas' => 20, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN KARAWANG', 'jumlah_puskesmas' => 50, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN BEKASI', 'jumlah_puskesmas' => 44, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN BANDUNG BARAT', 'jumlah_puskesmas' => 32, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KABUPATEN PANGANDARAN', 'jumlah_puskesmas' => 15, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KOTA BOGOR', 'jumlah_puskesmas' => 25, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KOTA SUKABUMI', 'jumlah_puskesmas' => 15, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KOTA BANDUNG', 'jumlah_puskesmas' => 80, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KOTA CIREBON', 'jumlah_puskesmas' => 22, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KOTA BEKASI', 'jumlah_puskesmas' => 48, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KOTA DEPOK', 'jumlah_puskesmas' => 38, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KOTA CIMAHI', 'jumlah_puskesmas' => 13, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KOTA TASIKMALAYA', 'jumlah_puskesmas' => 22, 'tahun' => 2025],
            ['nama_kabupaten_kota' => 'KOTA BANJAR', 'jumlah_puskesmas' => 10, 'tahun' => 2025],
        ];
    }

    public static function getProcessedData(?string $kabupaten = null, ?int $tahun = null): array
    {
        $result  = static::fetchFromApi();
        $payload = $result['payload'] ?? [];
        $records = $result['data'] ?? [];
        $years   = $payload['years'] ?? [2025];

        // Filter Tahun
        $selectedYear = ($tahun !== null && in_array($tahun, $years, true)) ? $tahun : (!empty($years) ? max($years) : 2025);
        if ($selectedYear !== null) {
            $recordsWithYear = array_filter($records, fn ($r) => ($r['tahun'] ?? null) === $selectedYear);
            if (!empty($recordsWithYear)) {
                $records = array_values($recordsWithYear);
            }
        }

        // Filter Kabupaten / Kota
        $kab = mb_strtoupper(trim((string) $kabupaten));
        if ($kab !== '' && $kab !== 'SEMUA') {
            $records = array_values(array_filter(
                $records,
                fn ($r) => mb_strtoupper(trim($r['nama_kabupaten_kota'] ?? '')) === $kab
            ));
        }

        $total   = array_sum(array_column($records, 'jumlah_puskesmas'));
        $largest = null;
        foreach ($records as $item) {
            if ($largest === null || ($item['jumlah_puskesmas'] ?? 0) > ($largest['jumlah_puskesmas'] ?? 0)) {
                $largest = [
                    'nama_kabupaten_kota' => $item['nama_kabupaten_kota'] ?? '-',
                    'jumlah_puskesmas'    => $item['jumlah_puskesmas'] ?? 0,
                ];
            }
        }

        $regionOptions = array_values(array_unique(array_map(
            fn ($r) => mb_strtoupper(trim($r['nama_kabupaten_kota'] ?? '')),
            $result['data'] ?? []
        )));
        if (empty($regionOptions)) {
            $regionOptions = config('puskesmas.reference_regions', []);
        }
        sort($regionOptions);

        return [
            'meta' => [
                'source'            => $result['source'] ?? 'LOCAL_FALLBACK_GUARD',
                'status'            => $result['status'] ?? 'fallback',
                'latency'           => $result['latency'] ?? 0,
                'fetched_at'        => $result['fetched_at'] ?? now()->toIso8601String(),
                'error'             => $result['error'] ?? null,
                'error_code'        => $result['error_code'] ?? null,
                'classification'    => 'VALID_LENGKAP',
                'quality'           => $result['quality'] ?? null,
                'warnings'          => [],
                'total_records'     => count($records),
                'period'            => ['selected' => $selectedYear, 'available' => $years],
                'kabupaten_options' => $regionOptions,
                'endpoint'          => config('puskesmas.aggregate.endpoint'),
            ],
            'summary' => [
                'total_puskesmas'   => $total,
                'total_wilayah'     => count($records),
                'wilayah_terbanyak' => $largest,
                'tahun'             => $selectedYear,
            ],
            'items' => $records,
        ];
    }
}