<?php

namespace App\Application\Product\UpdateProduct;

final class UpdateProductDTO
{
    public function __construct(
        public readonly string  $id,
        public readonly string  $name,
        public readonly string  $type,
        public readonly ?string $description = null,
    ) {}
}
