<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\QualityController;
use App\Http\Controllers\DuplicateDetectionController;
use App\Http\Controllers\UserProfileController;

// Search endpoint
Route::get('/search', [SearchController::class, 'search']);

// Data quality analysis endpoint
Route::get('/quality', [QualityController::class, 'analyze']);

// Duplicate Detection API
Route::get('/duplicates/find', [DuplicateDetectionController::class, 'find']);

// User Profile API (Multi-Table JOIN)
Route::get('/user-profile/{user_id}', [UserProfileController::class, 'getProfile'])
    ->where('user_id', '[0-9]+');

