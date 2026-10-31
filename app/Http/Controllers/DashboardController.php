<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PuskesmasJabar;
use Illuminate\Http\JsonResponse;

class DashboardController
{
    public function index(Request $request)
    {
        $selectedKabupaten = $request->query('kabupaten', 'SEMUA');
        $dashboardData = PuskesmasJabar::getProcessedData($selectedKabupaten);

        return view('dashboard', [
            'data' => $dashboardData,
            'selectedKabupaten' => $selectedKabupaten
        ]);
    }

    public function apiData(Request $request): JsonResponse
    {
        $selectedKabupaten = $request->query('kabupaten', 'SEMUA');
        $dashboardData = PuskesmasJabar::getProcessedData($selectedKabupaten);

        return response()->json($dashboardData);
    }
}