<?php

namespace App\Domain\Product\Ports;

use App\Domain\Product\Product;
use Ramsey\Uuid\UuidInterface;

interface ProductRepositoryPort
{
    public function save(Product $product): void;

    public function findById(UuidInterface $id): ?Product;

    /** @return Product[] */
    public function findAll(): array;

    /** @return Product[] */
    public function findInactive(): array;
}
