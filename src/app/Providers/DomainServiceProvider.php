<?php

namespace App\Providers;

use App\Domain\Product\Ports\ProductRepositoryPort;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryPort::class, EloquentProductRepository::class);
    }
}
