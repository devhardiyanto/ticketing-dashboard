<?php

use App\Http\Controllers\UserController as User;
use Illuminate\Support\Facades\Route;

$name = 'user';
Route::controller(User::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/data', 'data')->name("$name.data")->middleware('can:users.read');
        Route::get('/', 'index')->name("$name.index")->middleware('can:users.read');
        Route::get('{id}', 'show')->name("$name.show")->middleware('can:users.read')->where('id', '[0-9]+');
        Route::post('/', 'store')->name("$name.store")->middleware('can:users.create');
        Route::put('{id}', 'update')->name("$name.update")->middleware('can:users.update');
        Route::patch('{id}/toggle-status', 'toggleStatus')->name("$name.toggle-status")->middleware('can:users.update');
        Route::delete('{id}', 'destroy')->name("$name.destroy")->middleware('can:users.delete');
    });
