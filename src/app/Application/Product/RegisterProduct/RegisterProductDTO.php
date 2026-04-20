<?php

namespace App\Application\Product\RegisterProduct;

final class RegisterProductDTO
{
    /** @param RegisterVariantDTO[] $variants */
    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly array  $variants,
        public readonly ?string $description = null,
    ) {}
}
