<?php

namespace App\Infrastructure\Events\Listeners;

use App\Application\Stock\CheckMinimumStock\CheckMinimumStockHandler;
use App\Domain\Stock\Events\StockBelowMinimumDetected;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueuedStockAlertListener implements ShouldQueue
{
    public string $queue = 'stock-alerts';

    public function __construct(private CheckMinimumStockHandler $handler) {}

    public function handle(StockBelowMinimumDetected $event): void
    {
        $this->handler->handle($event);
    }
}
