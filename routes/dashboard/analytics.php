<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data', [AnalyticsController::class, 'data'])->name('analytics.data'); // New endpoint
    Route::post('/analytics/refresh', [AnalyticsController::class, 'refresh'])->name('analytics.refresh');
});
