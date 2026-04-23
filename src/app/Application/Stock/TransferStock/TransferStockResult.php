<?php

namespace App\Application\Stock\TransferStock;

use App\Domain\Stock\StockMovement;

final class TransferStockResult
{
    public function __construct(
        public readonly StockMovement $exit,
        public readonly StockMovement $entry,
    ) {}
}
