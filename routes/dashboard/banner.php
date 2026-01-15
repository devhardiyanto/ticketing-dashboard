<?php

use App\Http\Controllers\BannerController;
use Illuminate\Support\Facades\Route;

$name = 'banner';
Route::controller(BannerController::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/', 'index')->name("$name.index");
        Route::post('/', 'store')->name("$name.store");
        Route::put('{id}', 'update')->name("$name.update");
        Route::delete('{id}', 'destroy')->name("$name.destroy");
        Route::patch('{id}/toggle', 'toggle')->name("$name.toggle");
        Route::patch('/reorder', 'reorder')->name("$name.reorder");
    });
