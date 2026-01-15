<?php

use App\Http\Controllers\UserController as User;
use Illuminate\Support\Facades\Route;

$name = 'user';
Route::controller(User::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/data', 'data')->name("$name.data");
        Route::get('/', 'index')->name("$name.index");
        Route::post('/', 'store')->name("$name.store");
        Route::put('{id}', 'update')->name("$name.update");
        Route::patch('{id}/toggle-status', 'toggleStatus')->name("$name.toggle-status");
        Route::delete('{id}', 'destroy')->name("$name.destroy");
    });
