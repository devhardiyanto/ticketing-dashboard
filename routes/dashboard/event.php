<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController as Event;

$name = "event";
Route::controller(Event::class)
	->middleware(['auth', 'verified'])
	->prefix($name)
	->group(function () use ($name) {
		Route::get('/check-slug', 'checkSlug')->name("$name.check-slug");
		Route::get('/{event_id?}', 'index')->name("$name.index");
		Route::post('/', 'store')->name("$name.store");
		Route::put('{id}', 'update')->name("$name.update");
		Route::delete('{id}', 'destroy')->name("$name.destroy");
	});

