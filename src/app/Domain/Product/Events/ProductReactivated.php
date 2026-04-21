<?php

namespace App\Domain\Product\Events;

use Ramsey\Uuid\UuidInterface;

final class ProductReactivated
{
    public function __construct(
        public readonly UuidInterface $productId,
    ) {}
}
