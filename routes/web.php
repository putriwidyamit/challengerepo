<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/portal', function () {
    return view('portal');
});

Route::get('/search', function () {
    return view('search');
});

// Health Check Routes
Route::get('/health', [HealthController::class, 'check']);
Route::get('/health/dashboard', [HealthController::class, 'dashboard']);

// Quality Dashboard Route
Route::get('/quality', function () {
    return view('quality-dashboard');
});

// Duplicate Detection UI Route
Route::get('/duplicates', function () {
    return view('duplicates');
});
