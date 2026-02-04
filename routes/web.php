<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';

require __DIR__ . '/dashboard/event.php';
require __DIR__ . '/dashboard/ticket_type.php';
require __DIR__ . '/dashboard/upload.php';
require __DIR__ . '/dashboard/banner.php';
require __DIR__ . '/dashboard/platform_fee.php';
require __DIR__ . '/dashboard/order.php';
require __DIR__ . '/dashboard/user.php';
require __DIR__ . '/dashboard/organization.php';
require __DIR__ . '/dashboard/system.php';
require __DIR__ . '/dashboard/analytics.php';
require __DIR__ . '/dashboard/role.php';
require __DIR__ . '/dashboard/auth_cache.php';
require __DIR__ . '/dashboard/scanner.php';
require __DIR__ . '/dashboard/onground.php';
