<?php

use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

$name = 'upload';
Route::controller(UploadController::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::post('/', 'upload')->name("$name.store");
        Route::delete('/', 'delete')->name("$name.destroy");
        Route::post('/signed-url', 'generateSignedUrl')->name("$name.signed-url");
        Route::post('/signed-urls', 'generateSignedUrls')->name("$name.signed-urls");
    });
