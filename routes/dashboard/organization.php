<?php

use App\Http\Controllers\OrganizationController as Organization;
use Illuminate\Support\Facades\Route;

$name = 'organization';
Route::controller(Organization::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/', 'index')->name("$name.index")->middleware('can:organizations.read');
        Route::post('/', 'store')->name("$name.store")->middleware('can:organizations.create');
        Route::put('{id}', 'update')->name("$name.update")->middleware('can:organizations.update');
        Route::delete('{id}', 'destroy')->name("$name.destroy")->middleware('can:organizations.delete');
    });
