<?php

namespace App\Domain\Stock\Events;

use Ramsey\Uuid\UuidInterface;

class StockMovementReversed
{
    public function __construct(
        public readonly UuidInterface $reversalMovementId,
        public readonly UuidInterface $originalMovementId,
        public readonly UuidInterface $variantId,
    ) {}
}
