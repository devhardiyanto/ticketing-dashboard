<?php

use App\Http\Controllers\ScannerController as Scanner;
use Illuminate\Support\Facades\Route;

$name = 'scanner';
Route::controller(Scanner::class)
  ->middleware(['auth', 'verified'])
  ->prefix($name)
  ->group(function () use ($name) {
    // Scanner page
    Route::get('/', 'index')->name("$name.index")->middleware('can:scanner.access');
    Route::post('/validate', 'validate')->name("$name.validate")->middleware('can:scanner.access');

    // Scan history
    Route::get('/history', 'history')->name("$name.history")->middleware('can:scanner.history.read');
    Route::get('/history/data', 'historyData')->name("$name.history.data")->middleware('can:scanner.history.read');

    // Attendance dashboard
    Route::get('/attendance', 'attendance')->name("$name.attendance")->middleware('can:attendance.read');
    Route::get('/attendance/data', 'attendanceData')->name("$name.attendance.data")->middleware('can:attendance.read');
  });
