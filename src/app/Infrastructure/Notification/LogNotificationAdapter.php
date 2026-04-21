<?php

namespace App\Infrastructure\Notification;

use App\Domain\Shared\Ports\NotificationPort;
use Illuminate\Support\Facades\Log;

class LogNotificationAdapter implements NotificationPort
{
    public function sendStockAlert(string $variantId, int $currentBalance, int $minimumStock): void
    {
        Log::warning('Estoque abaixo do mínimo', [
            'variant_id'      => $variantId,
            'current_balance' => $currentBalance,
            'minimum_stock'   => $minimumStock,
        ]);
    }
}
