<?php

use App\Http\Controllers\OrganizationController as Organization;
use Illuminate\Support\Facades\Route;

$name = 'organization';
Route::controller(Organization::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/', 'index')->name("$name.index");
        Route::post('/', 'store')->name("$name.store");
        Route::put('{id}', 'update')->name("$name.update");
        Route::delete('{id}', 'destroy')->name("$name.destroy");
    });
