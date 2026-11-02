<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PuskesmasController;

// 1. Splash screen di tampilan awal (root), namanya tetap 'landing' agar navbar membaca tautannya
Route::view('/', 'landing')->name('landing');

// 2. Dashboard utama dipindah ke URL /beranda
Route::get('/beranda', [DashboardController::class, 'index'])->name('dashboard');

// 3. API dan Puskesmas dibiarkan tetap
Route::get('/api/v1/lensa-daerah', [DashboardController::class, 'apiData'])->name('api.lensa-daerah');
Route::get('/puskesmas', [PuskesmasController::class, 'index'])->name('puskesmas.index');
