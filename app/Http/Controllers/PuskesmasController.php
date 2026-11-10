<?php

namespace App\Http\Controllers;

use App\Models\DetailPuskesmas;
use Illuminate\Http\Request;

class PuskesmasController extends Controller
{
    public function index(Request $request)
    {
        $selectedKabupaten = trim((string) $request->query('kabupaten', 'SEMUA'));
        $searchQuery = trim((string) $request->query('q', ''));

        // Validasi koordinat agar input URL tidak menyebabkan perhitungan jarak yang salah.
        $lat = $request->query('lat');
        $lng = $request->query('lng');

        $userLat = is_numeric($lat) && (float) $lat >= -90 && (float) $lat <= 90
            ? (float) $lat
            : null;

        $userLng = is_numeric($lng) && (float) $lng >= -180 && (float) $lng <= 180
            ? (float) $lng
            : null;

        // Filter kabupaten/pencarian tidak boleh sekaligus membawa radius GPS.
        if ($selectedKabupaten !== 'SEMUA' || $searchQuery !== '') {
            $userLat = null;
            $userLng = null;
        }

        $data = DetailPuskesmas::getProcessedData(
            $selectedKabupaten,
            $searchQuery,
            $userLat,
            $userLng
        );

        return view('puskesmas.index', [
            'data' => $data,
            'selectedKabupaten' => $selectedKabupaten,
            'searchQuery' => $searchQuery,
            'userLat' => $userLat,
            'userLng' => $userLng,
        ]);
    }
}
