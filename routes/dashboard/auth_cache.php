<?php

use App\Http\Controllers\AuthCacheController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Cache Routes
|--------------------------------------------------------------------------
|
| Routes for client-side cache management.
| These routes provide API endpoints for refreshing auth cache data.
|
*/

Route::middleware(['auth', 'verified'])->prefix('api/auth')->group(function () {
  Route::get('/session-data', [AuthCacheController::class, 'getSessionData'])
    ->name('api.auth.session-data');
});
