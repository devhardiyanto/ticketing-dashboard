<?php

use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

$name = 'role';
Route::controller(RoleController::class)
    ->middleware(['auth', 'verified', \App\Http\Middleware\AutoCheckPermission::class])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/data', 'data')->name("$name.data");
        Route::get('/', 'index')->name("$name.index");
        Route::put('{id}', 'update')->name("$name.update");
    });
