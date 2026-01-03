<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
	return Inertia::render('Welcome', [
		'canRegister' => Features::enabled(Features::registration()),
	]);
})->name('home');

Route::get('dashboard', function () {
	return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';

require __DIR__ . '/dashboard/event.php';
require __DIR__ . '/dashboard/organization.php';
require __DIR__ . '/dashboard/ticket_type.php';
require __DIR__ . '/dashboard/upload.php';
