<?php

use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Exceptions\VariantNotFoundException;
use App\Domain\Stock\Exceptions\StockMovementNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ProductNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        });

        $exceptions->render(function (VariantNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        });

        $exceptions->render(function (StockMovementNotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        });

        // DomainException cobre: saldo insuficiente, estorno de estorno, etc.
        $exceptions->render(function (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        });

        // Violação de constraint única no banco (ex: SKU duplicado)
        $exceptions->render(function (UniqueConstraintViolationException $e) {
            return response()->json(['message' => 'Já existe um registro com esses dados.'], 422);
        });
    })->create();
