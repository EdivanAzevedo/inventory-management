<?php

use App\Infrastructure\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::post('/', [ProductController::class, 'store']);
    Route::get('/{id}', [ProductController::class, 'show']);
    Route::put('/{id}', [ProductController::class, 'update']);
    Route::delete('/{id}', [ProductController::class, 'destroy']);

    Route::post('/{productId}/variants', [ProductController::class, 'addVariant']);
    Route::delete('/{productId}/variants/{variantId}', [ProductController::class, 'removeVariant']);
});
