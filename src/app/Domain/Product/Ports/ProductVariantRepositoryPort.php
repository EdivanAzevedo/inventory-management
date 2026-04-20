<?php

namespace App\Domain\Product\Ports;

use App\Domain\Product\ProductVariant;
use Ramsey\Uuid\UuidInterface;

interface ProductVariantRepositoryPort
{
    public function findById(UuidInterface $id): ?ProductVariant;
}
