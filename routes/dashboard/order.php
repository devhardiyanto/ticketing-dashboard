<?php

use App\Http\Controllers\OrderController as Order;
use Illuminate\Support\Facades\Route;

$name = 'order';
Route::controller(Order::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/data', 'data')->name("$name.data")->middleware('can:orders.read');
        Route::get('/', 'index')->name("$name.index")->middleware('can:orders.read');
        Route::get('/{id}', 'show')->name("$name.show")->middleware('can:orders.read');
    });
