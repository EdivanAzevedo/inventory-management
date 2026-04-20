<?php

namespace App\Domain\Stock\Ports;

use App\Domain\Stock\StockMovement;
use Ramsey\Uuid\UuidInterface;

interface StockMovementRepositoryPort
{
    public function save(StockMovement $movement): void;

    public function findById(UuidInterface $id): ?StockMovement;

    /** @return StockMovement[] */
    public function findByVariantId(UuidInterface $variantId): array;
}
