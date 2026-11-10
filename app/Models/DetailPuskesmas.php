<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class DetailPuskesmas extends Model
{
    /**
     * Direktori detail memakai JSON lokal sebagai sumber utama.
     * Model ini sengaja TIDAK memanggil API agregat Jawa Barat.
     */
    private const JSON_FILE = 'Puskesmas_detail.json';
    private const GEOCODE_CACHE_DAYS = 30;
    private const NEGATIVE_CACHE_HOURS = 24;
    private const GEOCODE_BATCH_SIZE = 20;

    private static ?array $localData = null;

    private static function getAllLocalData(): array
    {
        if (self::$localData !== null) {
            return self::$localData;
        }

        $path = database_path('data/' . self::JSON_FILE);

        if (!File::exists($path)) {
            // Fallback untuk instalasi yang memakai variasi huruf besar/kecil nama file.
            $matches = File::glob(database_path('data/*puskesmas*detail*.json'));
            $path = $matches[0] ?? null;
        }

        if (!$path || !File::exists($path)) {
            return self::$localData = [];
        }

        $decoded = json_decode(File::get($path), true);

        if (!is_array($decoded)) {
            return self::$localData = [];
        }

        $rows = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }

            $name = trim((string) ($row['nama_puskesmas_asli'] ?? $row['nama_puskesmas'] ?? ''));
            $address = trim((string) ($row['alamat_asli'] ?? $row['alamat'] ?? ''));
            $county = trim((string) ($row['nama_kabupaten_kota'] ?? ''));

            // Jangan membuat record sintetis dari angka agregat API.
            if ($name === '' || $county === '') {
                continue;
            }

            $rows[] = [
                'kode_puskesmas' => trim((string) ($row['kode_puskesmas'] ?? '')),
                'nama_puskesmas_asli' => $name,
                'alamat_asli' => $address,
                'nama_kabupaten_kota' => mb_strtoupper($county),
                // Koordinat hanya dipakai jika memang sudah ada di JSON.
                'latitude' => self::validCoordinate($row['latitude'] ?? null, -90, 90),
                'longitude' => self::validCoordinate($row['longitude'] ?? null, -180, 180),
                'poned' => $row['poned'] ?? null,
                'jenis_puskesmas' => $row['jenis_puskesmas'] ?? null,
            ];
        }

        return self::$localData = $rows;
    }

    private static function validCoordinate(mixed $value, float $min, float $max): ?float
    {
        if (!is_numeric($value)) {
            return null;
        }

        $number = (float) $value;
        return ($number >= $min && $number <= $max) ? $number : null;
    }

    private static function extractDistrict(string $address): string
    {
        if (preg_match('/\bKec(?:amatan)?\.?\s*([^,]+)/iu', $address, $match)) {
            return mb_strtoupper(trim($match[1]));
        }

        return '-';
    }

    private static function buildMapsUrl(array $item): string
    {
        $destination = implode(', ', array_filter([
            $item['nama_puskesmas'] ?? '',
            $item['alamat'] ?? '',
            $item['nama_kabupaten_kota'] ?? '',
            'Jawa Barat',
            'Indonesia',
        ]));

        // URL pencarian tujuan: Google Maps akan mencari berdasarkan identitas/alamat JSON.
        // Ini bukan klaim bahwa koordinat telah diverifikasi.
        return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode($destination);
    }

    private static function geocodeCacheKey(array $item): string
    {
        $identity = implode('|', [
            $item['kode_puskesmas'] ?? '',
            $item['nama_puskesmas'] ?? '',
            $item['alamat'] ?? '',
            $item['nama_kabupaten_kota'] ?? '',
        ]);

        return 'lensa_daerah:puskesmas_geocode:v1:' . hash('sha256', mb_strtolower($identity));
    }

    /**
     * Geocode hanya diperlukan saat pengguna meminta pencarian radius.
     * Hasil Google Geocoding adalah kandidat koordinat, bukan verifikasi identitas fasilitas.
     * Respons disimpan di cache agar request berikutnya tidak mengulang biaya API.
     */
    private static function addGeocodingCandidates(array $items): array
    {
        $apiKey = config('services.google_maps.key') ?: env('GOOGLE_MAPS_API_KEY');

        if (!$apiKey) {
            foreach ($items as &$item) {
                $item['geocode_status'] = empty($item['latitude']) || empty($item['longitude'])
                    ? 'api_key_missing'
                    : 'json_coordinate';
            }
            unset($item);
            return $items;
        }

        $pending = [];

        foreach ($items as $index => &$item) {
            if ($item['latitude'] !== null && $item['longitude'] !== null) {
                $item['geocode_status'] = 'json_coordinate';
                continue;
            }

            $key = self::geocodeCacheKey($item);
            $cached = Cache::get($key, '__not_cached__');

            if ($cached !== '__not_cached__') {
                if (is_array($cached)) {
                    $item['latitude'] = $cached['lat'];
                    $item['longitude'] = $cached['lng'];
                    $item['geocode_status'] = 'google_candidate';
                    $item['geocode_address'] = $cached['formatted_address'] ?? null;
                } else {
                    $item['geocode_status'] = 'not_found';
                }
                continue;
            }

            $pending[$index] = [
                'cache_key' => $key,
                'query' => implode(', ', array_filter([
                    $item['nama_puskesmas'],
                    $item['alamat'],
                    $item['nama_kabupaten_kota'],
                    'Jawa Barat',
                    'Indonesia',
                ])),
            ];
        }
        unset($item);

        foreach (array_chunk($pending, self::GEOCODE_BATCH_SIZE, true) as $batch) {
            try {
                $responses = Http::pool(function (Pool $pool) use ($batch, $apiKey) {
                    $requests = [];
                    foreach ($batch as $index => $request) {
                        $requests[] = $pool->as((string) $index)
                            ->timeout(10)
                            ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                                'address' => $request['query'],
                                'key' => $apiKey,
                                'region' => 'id',
                                'language' => 'id',
                            ]);
                    }
                    return $requests;
                });

                foreach ($batch as $index => $request) {
                    $response = $responses[(string) $index] ?? null;
                    $payload = $response && $response->successful() ? $response->json() : [];
                    $result = $payload['results'][0] ?? null;

                    if (($payload['status'] ?? '') === 'OK' && is_array($result)) {
                        $location = $result['geometry']['location'] ?? [];
                        $lat = self::validCoordinate($location['lat'] ?? null, -90, 90);
                        $lng = self::validCoordinate($location['lng'] ?? null, -180, 180);

                        if ($lat !== null && $lng !== null) {
                            $value = [
                                'lat' => $lat,
                                'lng' => $lng,
                                'formatted_address' => $result['formatted_address'] ?? null,
                            ];
                            Cache::put($request['cache_key'], $value, now()->addDays(self::GEOCODE_CACHE_DAYS));
                            $items[$index]['latitude'] = $lat;
                            $items[$index]['longitude'] = $lng;
                            $items[$index]['geocode_status'] = 'google_candidate';
                            $items[$index]['geocode_address'] = $value['formatted_address'];
                            continue;
                        }
                    }

                    Cache::put($request['cache_key'], false, now()->addHours(self::NEGATIVE_CACHE_HOURS));
                    $items[$index]['geocode_status'] = 'not_found';
                }
            } catch (\Throwable $exception) {
                // Kegagalan Google tidak boleh menggagalkan halaman direktori.
                foreach ($batch as $index => $request) {
                    $items[$index]['geocode_status'] = 'geocode_error';
                }
            }
        }

        return $items;
    }

    private static function haversineDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371.0088;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($a)));
    }

    public static function getProcessedData(
        ?string $kabupaten = null,
        ?string $search = null,
        ?float $userLat = null,
        ?float $userLng = null
    ): array {
        $allRows = self::getAllLocalData();

        $kab = mb_strtoupper(trim((string) $kabupaten));
        $hasKabFilter = $kab !== '' && $kab !== 'SEMUA';
        $query = mb_strtolower(trim((string) $search));
        $hasSearchFilter = $query !== '';
        $hasLocation = $userLat !== null && $userLng !== null;

        // Kabupaten/kota dan pencarian selalu mengambil seluruh data JSON,
        // tanpa membawa filter radius GPS dari halaman sebelumnya.
        if ($hasKabFilter) {
            $allRows = array_values(array_filter(
                $allRows,
                fn ($row) => mb_strtoupper($row['nama_kabupaten_kota']) === $kab
            ));
        }

        $items = [];
        foreach ($allRows as $row) {
            $name = mb_strtoupper(trim($row['nama_puskesmas_asli']));
            $formattedName = str_starts_with($name, 'PUSKESMAS') ? $name : 'PUSKESMAS ' . $name;
            $address = trim((string) $row['alamat_asli']);

            $item = [
                'nama_puskesmas' => $formattedName,
                'kode_puskesmas' => $row['kode_puskesmas'],
                'nama_kecamatan' => self::extractDistrict($address),
                'nama_kabupaten_kota' => $row['nama_kabupaten_kota'],
                'alamat' => $address !== '' ? $address : 'Alamat belum tersedia di JSON',
                'poned' => ($row['poned'] ?? null) == '1' ? 'PONED' : null,
                'jenis_puskesmas' => ($row['jenis_puskesmas'] ?? null) == '1' ? 'Rawat Inap' : 'Non Rawat Inap',
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude'],
                'jarak_km' => null,
                'geocode_status' => ($row['latitude'] !== null && $row['longitude'] !== null)
                    ? 'json_coordinate'
                    : 'not_checked',
            ];

            $item['maps_url'] = self::buildMapsUrl($item);
            $items[] = $item;
        }

        if ($hasSearchFilter) {
            $items = array_values(array_filter($items, function ($item) use ($query) {
                $searchable = mb_strtolower(implode(' ', [
                    $item['nama_puskesmas'],
                    $item['kode_puskesmas'],
                    $item['nama_kecamatan'],
                    $item['nama_kabupaten_kota'],
                    $item['alamat'],
                ]));

                return str_contains($searchable, $query);
            }));
        }

        $radiusApplied = $hasLocation && !$hasKabFilter && !$hasSearchFilter;
        $geocodingConfigured = (bool) (config('services.google_maps.key') ?: env('GOOGLE_MAPS_API_KEY'));

        if ($radiusApplied) {
            $items = self::addGeocodingCandidates($items);

            foreach ($items as &$item) {
                if (is_numeric($item['latitude']) && is_numeric($item['longitude'])) {
                    $item['jarak_km'] = round(self::haversineDistance(
                        $userLat,
                        $userLng,
                        (float) $item['latitude'],
                        (float) $item['longitude']
                    ), 2);
                }
            }
            unset($item);

            $items = array_values(array_filter(
                $items,
                fn ($item) => $item['jarak_km'] !== null && $item['jarak_km'] <= 20
            ));

            usort($items, fn ($a, $b) => $a['jarak_km'] <=> $b['jarak_km']);
        } else {
            usort($items, fn ($a, $b) => strcmp($a['nama_puskesmas'], $b['nama_puskesmas']));
        }

        $options = config('puskesmas.reference_regions', []);
        if (!$options) {
            $options = array_values(array_unique(array_column(self::getAllLocalData(), 'nama_kabupaten_kota')));
            sort($options);
        }

        return [
            'meta' => [
                'source' => 'LOCAL_JSON',
                'total_records' => count($items),
                'total_json_records' => count(self::getAllLocalData()),
                'kabupaten_options' => $options,
                'radius_applied' => $radiusApplied,
                'geocoding_configured' => $geocodingConfigured,
                'location_available' => $hasLocation,
                'message' => $radiusApplied && !$geocodingConfigured
                    ? 'Google Maps Geocoding API belum dikonfigurasi. Radius belum dapat dihitung untuk data tanpa koordinat.'
                    : null,
            ],
            'items' => $items,
        ];
    }
}
