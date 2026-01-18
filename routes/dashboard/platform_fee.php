<?php

use App\Http\Controllers\PlatformFeeConfigController;
use Illuminate\Support\Facades\Route;

$name = 'platform_fee';
Route::controller(PlatformFeeConfigController::class)
    ->middleware(['auth', 'verified'])
    ->prefix('platform-fee')
    ->group(function () use ($name) {
        Route::get('/', 'index')->name("$name.index")->middleware('can:settings.read');
        Route::put('/', 'update')->name("$name.update")->middleware('can:settings.update');
    });
