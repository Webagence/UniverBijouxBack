<?php

use App\Http\Controllers\Api\Admin\AdminController;
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
    Route::get('/universes', [ProductController::class, 'universes']);
    Route::get('/new-arrivals', [ProductController::class, 'newArrivals']);
    Route::get('/bestsellers', [ProductController::class, 'bestsellers']);
    Route::get('/{slug}', [ProductController::class, 'show']);
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

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Accounts
        Route::get('/accounts', [AdminController::class, 'accounts']);
        Route::put('/accounts/{id}/approve', [AdminController::class, 'toggleApproveAccount']);
        Route::delete('/accounts/{id}', [AdminController::class, 'deleteAccount']);

        // Orders
        Route::get('/orders', [AdminController::class, 'orders']);
        Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);

        // Products
        Route::get('/products', [AdminController::class, 'products']);
        Route::post('/products', [AdminController::class, 'storeProduct']);
        Route::put('/products/{id}', [AdminController::class, 'updateProduct']);
        Route::delete('/products/{id}', [AdminController::class, 'deleteProduct']);

        // Universes
        Route::get('/universes', [AdminController::class, 'universes']);
        Route::post('/universes', [AdminController::class, 'storeUniverse']);
        Route::put('/universes/{id}', [AdminController::class, 'updateUniverse']);
        Route::delete('/universes/{id}', [AdminController::class, 'deleteUniverse']);

        // Content
        Route::put('/content/hero', [AdminController::class, 'updateHero']);
        Route::put('/content/atelier', [AdminController::class, 'updateAtelier']);
        Route::put('/settings', [AdminController::class, 'updateSettings']);

        // Testimonials & FAQ
        Route::post('/testimonials/sync', [AdminController::class, 'syncTestimonials']);
        Route::post('/faq/sync', [AdminController::class, 'syncFaq']);

        // Tickets
        Route::get('/tickets', [AdminController::class, 'tickets']);
    });
});
