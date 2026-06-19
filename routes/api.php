<?php

use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/feed', [FeedController::class, 'index']);

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1');
});

Route::apiResource('posts', PostController::class)->only(['show']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Posts
    Route::post('/posts/preview', [PostController::class, 'preview']);
    Route::apiResource('posts', PostController::class)->except(['show', 'index']);

    // Admin Routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('posts', AdminPostController::class)->only(['index', 'show']);
        Route::patch('/posts/{post}/approve', [AdminPostController::class, 'approve']);
        Route::patch('/posts/{post}/reject', [AdminPostController::class, 'reject']);
    });

});