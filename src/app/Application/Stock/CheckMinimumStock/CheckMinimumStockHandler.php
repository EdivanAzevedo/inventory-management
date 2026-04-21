<?php

namespace App\Application\Stock\CheckMinimumStock;

use App\Domain\Shared\Ports\NotificationPort;
use App\Domain\Stock\Events\StockBelowMinimumDetected;
use Illuminate\Contracts\Queue\ShouldQueue;

class CheckMinimumStockHandler implements ShouldQueue
{
    public string $queue = 'stock-alerts';

    public function __construct(private NotificationPort $notifications) {}

    public function handle(StockBelowMinimumDetected $event): void
    {
        $this->notifications->sendStockAlert(
            variantId:      $event->variantId->toString(),
            currentBalance: $event->currentBalance,
            minimumStock:   $event->minimumStock,
        );
    }
}
