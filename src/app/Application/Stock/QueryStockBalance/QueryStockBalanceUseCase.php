<?php

namespace App\Application\Stock\QueryStockBalance;

use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\StockBalance;
use Ramsey\Uuid\Uuid;

class QueryStockBalanceUseCase
{
    public function __construct(
        private StockBalanceRepositoryPort $balances,
    ) {}

    public function execute(string $variantId): StockBalance
    {
        return $this->balances->getBalanceByVariantId(Uuid::fromString($variantId));
    }
}
