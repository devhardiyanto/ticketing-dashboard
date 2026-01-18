<?php

use App\Http\Controllers\UserController as User;
use Illuminate\Support\Facades\Route;

$name = 'user';
Route::controller(User::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/data', 'data')->name("$name.data")->middleware('can:dashboard_users.read');
        Route::get('/', 'index')->name("$name.index")->middleware('can:dashboard_users.read');
        Route::post('/', 'store')->name("$name.store")->middleware('can:dashboard_users.create');
        Route::put('{id}', 'update')->name("$name.update")->middleware('can:dashboard_users.update');
        Route::patch('{id}/toggle-status', 'toggleStatus')->name("$name.toggle-status")->middleware('can:dashboard_users.update');
        Route::delete('{id}', 'destroy')->name("$name.destroy")->middleware('can:dashboard_users.delete');
    });
