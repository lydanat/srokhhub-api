<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FeedController;
use Illuminate\Support\Facades\Route;

Route::get('/feed', [FeedController::class, 'index']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        // admin routes go here later
    });

});