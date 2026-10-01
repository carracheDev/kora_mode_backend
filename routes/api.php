<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\FavoriteController;
use App\Http\Controllers\Api\V1\FedaPayWebhookController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PaymentSimulationController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

    Route::prefix('auth')->name('auth.')->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:6,1')->name('register');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1')->name('login');

        Route::middleware('auth:sanctum')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });
    });

    Route::post('/webhooks/fedapay', FedaPayWebhookController::class)->name('webhooks.fedapay');

    Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:30,1')->name('orders.store');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{orderNumber}/payments', [PaymentController::class, 'store'])->middleware('throttle:10,1')->name('orders.payments.store');
    Route::post('/orders/{orderNumber}/payment-simulation', [PaymentSimulationController::class, 'store'])->middleware('throttle:10,1')->name('orders.payment-simulation.store');
    Route::get('/orders', [OrderController::class, 'index'])->middleware('auth:sanctum')->name('orders.index');

    Route::middleware('auth:sanctum')->prefix('favorites')->name('favorites.')->group(function (): void {
        Route::get('/', [FavoriteController::class, 'index'])->name('index');
        Route::post('/', [FavoriteController::class, 'store'])->name('store');
        Route::delete('/{slug}', [FavoriteController::class, 'destroy'])->name('destroy');
    });
});
