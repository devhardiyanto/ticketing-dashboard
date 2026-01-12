<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController as User;

$name = "organization.user";

// Scoped User Management for Organization
// Uses UserController but likely renders a different view or context
Route::controller(User::class)
	->middleware(['auth', 'verified'])
	->prefix('organization-users')
	->group(function () use ($name) {
		Route::get('/', 'index')->name("$name.index");
	});
