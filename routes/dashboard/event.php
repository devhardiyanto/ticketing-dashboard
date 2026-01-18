<?php

use App\Http\Controllers\EventController as Event;
use Illuminate\Support\Facades\Route;

$name = 'event';
Route::controller(Event::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/check-slug', 'checkSlug')->name("$name.check-slug")->middleware('can:events.create');
        Route::get('/data', 'data')->name("$name.data")->middleware('can:events.read');
        Route::get('/{event_id?}', 'index')->name("$name.index")->middleware('can:events.read');
        Route::post('/', 'store')->name("$name.store")->middleware('can:events.create');
        Route::put('{id}', 'update')->name("$name.update")->middleware('can:events.update');
        Route::delete('{id}', 'destroy')->name("$name.destroy")->middleware('can:events.delete');
    });
