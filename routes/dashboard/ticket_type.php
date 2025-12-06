<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketTypeController as TicketType;


Route::apiResource('ticket_type', TicketType::class)
	->middleware(['auth', 'verified'])
	->except(['show']);
