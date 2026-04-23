<?php

namespace App\Providers;

use App\Domain\Stock\Events\StockBelowMinimumDetected;
use App\Infrastructure\Events\Listeners\QueuedStockAlertListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(StockBelowMinimumDetected::class, QueuedStockAlertListener::class);
    }
}
