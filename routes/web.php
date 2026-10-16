<?php

use App\Http\Controllers\Admin\AlokasiController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\LokerController as AdminLokerController;
use App\Http\Controllers\LokerController;
use App\Http\Controllers\LokerScanController;
use Illuminate\Support\Facades\Route;

// Kiosk / Terminal Loker Utama
Route::get('/', [LokerController::class, 'index'])->name('loker.index');

// API Kiosk & Scanner
Route::prefix('api/loker')->group(function () {
    Route::post('/scan', [LokerScanController::class, 'scan'])->name('loker.scan');
    Route::get('/status', [LokerScanController::class, 'status'])->name('loker.status');
    Route::post('/{id}/toggle-active', [LokerScanController::class, 'toggleActive'])->name('loker.toggle-active');
});

// Admin Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Master Karyawan CRUD
        Route::post('/karyawan/sync', [KaryawanController::class, 'sync'])->name('karyawan.sync');
        Route::resource('karyawan', KaryawanController::class)->except(['create', 'show', 'edit']);

        // Master Loker CRUD
        Route::post('/loker/{loker}/toggle-active', [AdminLokerController::class, 'toggleActive'])->name('loker.toggle-active');
        Route::resource('loker', AdminLokerController::class)->except(['create', 'show', 'edit']);

        // Alokasi & Riwayat Transaksi
        Route::get('/alokasi', [AlokasiController::class, 'index'])->name('alokasi.index');
        Route::post('/alokasi/{alokasi}/force-release', [AlokasiController::class, 'forceRelease'])->name('alokasi.force-release');
    });
});
