<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContentController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ShippingboController;
use App\Http\Controllers\Api\ShippingboWebhookController;
use App\Http\Controllers\Api\StripePaymentController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\TranslationController;
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
    Route::get('/promises', [ContentController::class, 'promises']);
    Route::get('/categories_section', [ContentController::class, 'categoriesSection']);
    Route::get('/product_grid_section', [ContentController::class, 'productGridSection']);
    Route::get('/new_by_universe_section', [ContentController::class, 'newByUniverseSection']);
    Route::get('/testimonials_section', [ContentController::class, 'testimonialsSection']);
    Route::get('/legal/{page}', [ContentController::class, 'legalPage']);
    Route::get('/contact_page', [ContentController::class, 'contactPage']);
    Route::get('/faq_page_header', [ContentController::class, 'faqPageHeader']);
});

// Public product routes
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/universes', [ProductController::class, 'universes']);
    Route::get('/new-arrivals', [ProductController::class, 'newArrivals']);
    Route::get('/bestsellers', [ProductController::class, 'bestsellers']);
    Route::get('/{slug}', [ProductController::class, 'show']);
});

// Stripe webhook (must be outside auth middleware, verified by signature)
Route::post('/stripe/webhook', [StripePaymentController::class, 'webhook']);

// Shippingbo webhook (public, receives webhooks from Shippingbo)
Route::post('/shippingbo/webhook', [ShippingboWebhookController::class, 'handle']);

// Shippingbo OAuth callback (public, receives OAuth redirect)
Route::get('/shippingbo/callback', [ShippingboController::class, 'handleCallback']);

// Translation routes (public for locale detection, admin for management)
Route::prefix('translations')->group(function () {
    Route::get('/locales', [TranslationController::class, 'locales']);
    Route::get('/current', [TranslationController::class, 'getCurrentLocale']);
    Route::post('/set-locale', [TranslationController::class, 'setLocale']);
    Route::post('/translate', [TranslationController::class, 'translate']);
});

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
        });

        // Discounts
        Route::prefix('discounts')->group(function () {
            Route::post('/validate', [DiscountController::class, 'validate']);
        });

    // Testimonials (pro users can submit)
    Route::get('/my-testimonials', [ContentController::class, 'myTestimonials']);
    Route::post('/testimonials', [ContentController::class, 'submitTestimonial']);

    // Orders (require approved account)
    Route::middleware('approved')->prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
        Route::get('/{id}/invoice', [OrderController::class, 'downloadInvoice']);
    });

    // Stripe payments (require approved account)
    Route::middleware('approved')->prefix('stripe')->group(function () {
        Route::post('/create-payment-intent', [StripePaymentController::class, 'createPaymentIntent']);
        Route::post('/confirm', [StripePaymentController::class, 'confirmPayment']);
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
        Route::post('/orders/{id}/generate-invoice', [AdminController::class, 'generateInvoice']);

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

        // Shippingbo
        Route::prefix('shippingbo')->group(function () {
            Route::get('/settings', [ShippingboController::class, 'getSettings']);
            Route::put('/settings', [ShippingboController::class, 'updateSettings']);
            Route::get('/authorize', [ShippingboController::class, 'getAuthorizationUrl']);
            Route::get('/sync-status', [ShippingboController::class, 'getSyncStatus']);
            Route::post('/sync/products', [ShippingboController::class, 'syncAllProducts']);
            Route::post('/sync/products/{productId}', [ShippingboController::class, 'syncProduct']);
            Route::post('/sync/orders/{orderId}', [ShippingboController::class, 'syncOrder']);
        });

        // Translations
        Route::prefix('translations')->group(function () {
            Route::get('/batches', [TranslationController::class, 'getBatches']);
            Route::get('/batches/{batchId}', [TranslationController::class, 'getBatch']);
            Route::post('/batches', [TranslationController::class, 'createBatch']);
            Route::get('/{modelType}/{modelId}', [TranslationController::class, 'getModelTranslations']);
            Route::put('/{modelType}/{modelId}', [TranslationController::class, 'updateTranslation']);
            Route::post('/{modelType}/{modelId}/translate', [TranslationController::class, 'translateModel']);
            Route::post('/translate-all', [TranslationController::class, 'translateAllModels']);
            Route::post('/clear-cache', [TranslationController::class, 'clearCache']);
        });
    });
});
