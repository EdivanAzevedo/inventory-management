<?php

namespace App\Domain\Shared\Ports;

interface NotificationPort
{
    public function sendStockAlert(string $variantId, int $currentBalance, int $minimumStock): void;
}
