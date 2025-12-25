<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketTypeController as TicketType;

$name = "ticket_type";
Route::controller(TicketType::class)
	->middleware(['auth', 'verified'])
	->prefix($name)
	->group(function () use ($name) {
		Route::get('/', 'index')->name("$name.index");
		Route::get('{event_id?}', 'list')->name("$name.list");
		Route::post('{event_id?}', 'store')->name("$name.store");
		Route::put('{event_id?}/{id}', 'update')->name("$name.update");
		Route::delete('{event_id?}/{id}', 'destroy')->name("$name.destroy");
	});
