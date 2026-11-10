<?php

namespace App\Services;

use App\Models\PuskesmasCoordinate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PuskesmasCoordinateService
{
    /**
     * METODE MANUAL: Admin menyimpan koordinat hasil tinjauan pasti.
     */
    public function updateManualCoordinate(string $kodePuskesmas, float $lat, float $lng): PuskesmasCoordinate
    {
        return PuskesmasCoordinate::updateOrCreate(
            ['kode_puskesmas' => $kodePuskesmas],
            [
                'latitude'          => $lat,
                'longitude'         => $lng,
                'status_verifikasi' => 'verified', // Langsung terverifikasi karena manual
                'metode_input'      => 'manual',
            ]
        );
    }

    /**
     * METODE OTOMATIS: Mencari koordinat via Google Maps Geocoding API.
     */
    public function fetchAutomaticGeocoding(string $kodePuskesmas, string $namaPuskesmas, string $alamat): ?PuskesmasCoordinate
    {
        $apiKey = config('services.google_maps.key');
        if (empty($apiKey)) return null;

        // Cek jika sudah ada koordinat verified, jangan ditimpa
        $existing = PuskesmasCoordinate::where('kode_puskesmas', $kodePuskesmas)->first();
        if ($existing && $existing->status_verifikasi === 'verified') {
            return $existing; 
        }

        $queryAddress = "{$namaPuskesmas}, {$alamat}, Jawa Barat, Indonesia";

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $queryAddress,
                'key'     => $apiKey,
            ]);

            $result = $response->json();

            if ($response->successful() && $result['status'] === 'OK') {
                $location = $result['results'][0]['geometry']['location'];
                
                return PuskesmasCoordinate::updateOrCreate(
                    ['kode_puskesmas' => $kodePuskesmas],
                    [
                        'latitude'          => $location['lat'],
                        'longitude'         => $location['lng'],
                        'status_verifikasi' => 'pending', // Wajib pending sampai diverifikasi
                        'metode_input'      => 'otomatis',
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error("Geocoding gagal untuk Puskesmas {$kodePuskesmas}: " . $e->getMessage());
        }

        return null;
    }
}