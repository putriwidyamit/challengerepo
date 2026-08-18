<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

Route::get('/', function () {
    return view('welcome');
});

// Health Check Routes
Route::get('/health', [HealthController::class, 'check']);
Route::get('/health/dashboard', [HealthController::class, 'dashboard']);
