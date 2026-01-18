<?php

use App\Http\Controllers\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/analytics/data', [AnalyticsController::class, 'data'])->name('analytics.data');
    Route::post('/analytics/refresh', [AnalyticsController::class, 'refresh'])->name('analytics.refresh');
    Route::get('/analytics/ranking', [AnalyticsController::class, 'ranking'])->name('analytics.ranking');
});
