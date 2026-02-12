<?php

use App\Http\Controllers\AttendeeController;
use App\Http\Controllers\OngroundSaleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
	Route::get('/attendees', [AttendeeController::class, 'index'])->name('attendees.index');
	Route::post('/attendees/{id}/check-in', [AttendeeController::class, 'checkIn'])->name('attendees.checkin');

	// Onground Sales & POS
	Route::get('/onground/sales', [OngroundSaleController::class, 'index'])->name('onground.sales.index');
	Route::get('/onground/sales/pos', [OngroundSaleController::class, 'create'])->name('onground.sales.pos');
	Route::post('/onground/sales', [OngroundSaleController::class, 'store'])->name('onground.sales.store');
	Route::get('/onground/events/{eventId}/items', [OngroundSaleController::class, 'items'])->name('onground.sales.items');
});
