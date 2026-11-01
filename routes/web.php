<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PuskesmasController;

// 1. Root '/' diarahkan ke view splash screen (misalnya view 'landing')
Route::view('/', 'landing')->name('splash');

// 2. Halaman dashboard utama dipindah ke '/beranda'
Route::get('/beranda', [DashboardController::class, 'index'])->name('dashboard');

// 3. API dan Puskesmas dibiarkan tetap
Route::get('/api/v1/lensa-daerah', [DashboardController::class, 'apiData'])->name('api.lensa-daerah');
Route::get('/puskesmas', [PuskesmasController::class, 'index'])->name('puskesmas.index');