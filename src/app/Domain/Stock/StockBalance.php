<?php

namespace App\Domain\Stock;

use Ramsey\Uuid\UuidInterface;

class StockBalance
{
    public function __construct(
        private UuidInterface $variantId,
        private int $quantity,
    ) {}

    public function getVariantId(): UuidInterface { return $this->variantId; }
    public function getQuantity(): int            { return $this->quantity; }
}
