<?php

namespace App\Application\Stock\CheckMinimumStock;

use App\Domain\Shared\Ports\NotificationPort;
use App\Domain\Stock\Events\StockBelowMinimumDetected;

class CheckMinimumStockHandler
{
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
