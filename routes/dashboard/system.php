<?php

use App\Http\Controllers\System\ActivityLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('activity-logs/data', [ActivityLogController::class, 'data'])->name('activity-logs.data')->middleware('can:activity_logs.read');
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index')->middleware('can:activity_logs.read');
});
