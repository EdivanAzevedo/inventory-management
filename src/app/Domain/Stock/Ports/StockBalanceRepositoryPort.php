<?php

namespace App\Domain\Stock\Ports;

use App\Domain\Stock\StockBalance;
use Ramsey\Uuid\UuidInterface;

interface StockBalanceRepositoryPort
{
    public function getBalanceByVariantId(UuidInterface $variantId): StockBalance;
}
