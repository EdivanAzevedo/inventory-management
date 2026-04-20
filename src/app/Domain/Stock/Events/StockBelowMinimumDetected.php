<?php

namespace App\Domain\Stock\Events;

use Ramsey\Uuid\UuidInterface;

class StockBelowMinimumDetected
{
    public function __construct(
        public readonly UuidInterface $variantId,
        public readonly int $currentBalance,
        public readonly int $minimumStock,
    ) {}
}
