<?php

namespace App\Providers;

use App\Application\Stock\CheckMinimumStock\CheckMinimumStockHandler;
use App\Domain\Stock\Events\StockBelowMinimumDetected;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(StockBelowMinimumDetected::class, CheckMinimumStockHandler::class);
    }
}
