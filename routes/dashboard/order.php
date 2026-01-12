<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController as Order;

$name = "order";
Route::controller(Order::class)
	->middleware(['auth', 'verified'])
	->prefix($name)
	->group(function () use ($name) {
		Route::get('/', 'index')->name("$name.index");
		Route::get('/{id}', 'show')->name("$name.show");
	});
