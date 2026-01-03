<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;

$name = "upload";
Route::controller(UploadController::class)
	->middleware(['auth', 'verified'])
	->prefix($name)
	->group(function () use ($name) {
		Route::post('/', 'upload')->name("$name.store");
		Route::delete('/', 'delete')->name("$name.destroy");
		Route::post('/signed-url', 'getSignedUrl')->name("$name.signed-url");
		Route::post('/signed-urls', 'getSignedUrls')->name("$name.signed-urls");
	});
