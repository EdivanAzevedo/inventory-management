<?php

namespace App\Application\Product\RegisterProduct;

final class RegisterVariantDTO
{
    public function __construct(
        public readonly string  $sku,
        public readonly string  $unit,
        public readonly int     $minimumStock,
        public readonly ?string $color = null,
        public readonly ?string $size  = null,
    ) {}
}
