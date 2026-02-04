<?php

use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\OngroundSaleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/attendees', [AttendeeController::class, 'index'])->name('attendees.index');
  Route::post('/attendees/{id}/check-in', [AttendeeController::class, 'checkIn'])->name('attendees.checkin');
  Route::get('/onground/sales', OngroundSaleController::class)->name('onground.sales.index'); // Still invokable
});
