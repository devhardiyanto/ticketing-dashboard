<?php

use App\Http\Controllers\TicketTypeController as TicketType;
use Illuminate\Support\Facades\Route;

$name = 'ticket_type';
Route::controller(TicketType::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/data', 'data')->name("$name.data");
        Route::get('/', 'index')->name("$name.index");
        Route::post('/', 'store')->name("$name.store");
        Route::put('/{id}', 'update')->name("$name.update");
        Route::delete('/{id}', 'destroy')->name("$name.destroy");
    });
