<?php

namespace App\Application\Product\AddProductVariant;

final class AddProductVariantDTO
{
    public function __construct(
        public readonly string  $productId,
        public readonly string  $sku,
        public readonly string  $unit,
        public readonly int     $minimumStock,
        public readonly ?string $color = null,
        public readonly ?string $size  = null,
    ) {}
}
