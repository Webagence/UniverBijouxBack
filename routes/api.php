<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public content routes
Route::prefix('content')->group(function () {
    Route::get('/hero', [ContentController::class, 'hero']);
    Route::get('/atelier', [ContentController::class, 'atelier']);
    Route::get('/testimonials', [ContentController::class, 'testimonials']);
    Route::get('/faq', [ContentController::class, 'faq']);
    Route::get('/settings', [ContentController::class, 'settings']);
});

// Public product routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{slug}', [ProductController::class, 'show']);
    Route::get('/universes', [ProductController::class, 'universes']);
    Route::get('/new-arrivals', [ProductController::class, 'newArrivals']);
    Route::get('/bestsellers', [ProductController::class, 'bestsellers']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
    });

    // Orders (require approved account)
    Route::middleware('approved')->prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
    });

    // Tickets
    Route::prefix('tickets')->group(function () {
        Route::get('/', [TicketController::class, 'index']);
        Route::post('/', [TicketController::class, 'store']);
        Route::get('/{id}', [TicketController::class, 'show']);
        Route::post('/{id}/reply', [TicketController::class, 'reply']);
    });

    // Upload (require admin)
    Route::middleware('role:admin')->prefix('uploads')->group(function () {
        Route::post('/image', [UploadController::class, 'image']);
        Route::post('/multiple', [UploadController::class, 'multiple']);
    });
});
