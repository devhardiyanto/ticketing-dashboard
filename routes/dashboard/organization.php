<?php

use App\Http\Controllers\OrganizationController as Organization;
use App\Http\Controllers\UserController as User;
use Illuminate\Support\Facades\Route;

$name = 'organization';
Route::controller(Organization::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/data', 'data')->name("$name.data")->middleware('can:organizations.read');
        Route::get('/', 'index')->name("$name.index")->middleware('can:organizations.read');
        Route::get('/{id}/show', 'show')->name("$name.show")->middleware('can:organizations.read');
        Route::post('/', 'store')->name("$name.store")->middleware('can:organizations.create');
        Route::put('{id}', 'update')->name("$name.update")->middleware('can:organizations.update');
        Route::delete('{id}', 'destroy')->name("$name.destroy")->middleware('can:organizations.delete');

		$name = 'organization.user';
		Route::controller(User::class)
			->middleware(['auth', 'verified'])
			->prefix('users')
			->group(function () use ($name) {
				Route::get('/data', 'data')->name("$name.data")->middleware('can:organizations.users.manage');
				Route::get('/', 'index')->name("$name.index")->middleware('can:organizations.users.manage');
			});
	});
