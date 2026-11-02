<?php
// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\DashboardController;
// use App\Http\Controllers\PuskesmasController;
use Illuminate\Support\Facades\Http;

// // 1. Splash screen di tampilan awal (root), namanya tetap 'landing' agar navbar membaca tautannya
// Route::view('/', 'landing')->name('landing');

// // 2. Dashboard utama dipindah ke URL /beranda
// Route::get('/beranda', [DashboardController::class, 'index'])->name('dashboard');

// // 3. API dan Puskesmas dibiarkan tetap
// Route::get('/api/v1/lensa-daerah', [DashboardController::class, 'apiData'])->name('api.lensa-daerah');
// Route::get('/puskesmas', [PuskesmasController::class, 'index'])->name('puskesmas.index');


Route::get('/debug-api', function () {
    $out = [];
    foreach (['aggregate', 'detail'] as $key) {
        $url = config("puskesmas.$key.endpoint");
        try {
            $r = Http::timeout(10)->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept'     => 'application/json',
            ])->get($url, ['per_page' => 1]);

            $out[$key] = [
                'url'    => $url,
                'status' => $r->status(),
                'body'   => mb_substr($r->body(), 0, 300),
            ];
        } catch (\Throwable $e) {
            $out[$key] = ['url' => $url, 'error' => $e->getMessage()];
        }
    }
    return response()->json($out);
});