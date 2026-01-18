<?php

use App\Http\Controllers\TicketTypeController as TicketType;
use Illuminate\Support\Facades\Route;

$name = 'ticket_type';
Route::controller(TicketType::class)
    ->middleware(['auth', 'verified'])
    ->prefix($name)
    ->group(function () use ($name) {
        Route::get('/data', 'data')->name("$name.data")->middleware('can:tickets.read');
        Route::get('/', 'index')->name("$name.index")->middleware('can:tickets.read');
        Route::post('/', 'store')->name("$name.store")->middleware('can:tickets.create');
        Route::put('/{id}', 'update')->name("$name.update")->middleware('can:tickets.update');
        Route::delete('/{id}', 'destroy')->name("$name.destroy")->middleware('can:tickets.delete');
    });
