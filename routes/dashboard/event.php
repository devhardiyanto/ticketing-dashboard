<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EventController as Event;


Route::apiResource('event', Event::class)
  ->middleware(['auth', 'verified'])
  ->except(['show']);
