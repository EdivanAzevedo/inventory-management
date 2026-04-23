<?php

namespace App\Providers;

use App\Domain\Product\Product;
use App\Domain\Stock\StockMovement;
use App\Domain\Stock\Events\StockBelowMinimumDetected;
use App\Domain\User\User;
use App\Infrastructure\Events\Listeners\QueuedStockAlertListener;
use App\Infrastructure\Http\Policies\ProductPolicy;
use App\Infrastructure\Http\Policies\StockPolicy;
use App\Infrastructure\Http\Policies\UserPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(StockBelowMinimumDetected::class, QueuedStockAlertListener::class);

        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(StockMovement::class, StockPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
