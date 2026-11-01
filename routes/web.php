<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PuskesmasController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/api/v1/lensa-daerah', [DashboardController::class, 'apiData'])->name('api.lensa-daerah');

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/puskesmas', [PuskesmasController::class, 'index'])->name('puskesmas.index');
Route::view('/beranda', 'landing')->name('landing');
