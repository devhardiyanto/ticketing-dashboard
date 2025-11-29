<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController as Organization;


Route::apiResource('organization', Organization::class)
  ->middleware(['auth', 'verified'])
  ->except(['show']);
