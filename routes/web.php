<?php

use App\Http\Controllers\LokerController;
use App\Http\Controllers\LokerScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LokerController::class, 'index'])->name('loker.index');

Route::prefix('api/loker')->group(function () {
    Route::post('/scan', [LokerScanController::class, 'scan'])->name('loker.scan');
    Route::get('/status', [LokerScanController::class, 'status'])->name('loker.status');
    Route::post('/{id}/toggle-active', [LokerScanController::class, 'toggleActive'])->name('loker.toggle-active');
});
