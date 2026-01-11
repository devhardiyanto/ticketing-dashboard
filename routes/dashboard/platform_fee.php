<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PlatformFeeConfigController;

$name = "platform_fee";
Route::controller(PlatformFeeConfigController::class)
	->middleware(['auth', 'verified'])
	->prefix('platform-fee')
	->group(function () use ($name) {
		Route::get('/', 'index')->name("$name.index");
		Route::put('/', 'update')->name("$name.update");
	});
