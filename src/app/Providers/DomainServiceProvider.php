<?php

namespace App\Providers;

use App\Domain\Product\Ports\ProductRepositoryPort;
use App\Domain\Product\Ports\ProductVariantRepositoryPort;
use App\Domain\Shared\Ports\IdGeneratorPort;
use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Infrastructure\Identity\UuidV4Generator;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductVariantRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockBalanceRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentStockMovementRepository;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(IdGeneratorPort::class, UuidV4Generator::class);

        $this->app->bind(ProductRepositoryPort::class, EloquentProductRepository::class);
        $this->app->bind(ProductVariantRepositoryPort::class, EloquentProductVariantRepository::class);
        $this->app->bind(StockMovementRepositoryPort::class, EloquentStockMovementRepository::class);
        $this->app->bind(StockBalanceRepositoryPort::class, EloquentStockBalanceRepository::class);
    }
}
