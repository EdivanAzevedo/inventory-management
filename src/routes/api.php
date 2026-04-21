<?php

use App\Infrastructure\Http\Controllers\Product\ProductController;
use App\Infrastructure\Http\Controllers\Stock\StockController;
use Illuminate\Support\Facades\Route;

Route::prefix('stock')->group(function () {
    Route::post('/entries', [StockController::class, 'entry']);
    Route::post('/exits', [StockController::class, 'exit']);
    Route::post('/movements/{id}/cancel', [StockController::class, 'cancel']);
    Route::get('/balance/{variantId}', [StockController::class, 'balance']);
    Route::get('/movements/{variantId}', [StockController::class, 'movements']);
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/inactive', [ProductController::class, 'inactive']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);
    Route::post('/{id}/reactivate', [ProductController::class, 'reactivate']);

    Route::post('/{productId}/variants', [ProductController::class, 'addVariant']);
    Route::delete('/{productId}/variants/{variantId}', [ProductController::class, 'removeVariant']);
});
