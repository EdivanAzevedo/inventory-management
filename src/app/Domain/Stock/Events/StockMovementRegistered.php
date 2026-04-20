<?php

namespace App\Domain\Stock\Events;

use Ramsey\Uuid\UuidInterface;

class StockMovementRegistered
{
    public function __construct(
        public readonly UuidInterface $movementId,
        public readonly UuidInterface $variantId,
    ) {}
}
