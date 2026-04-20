<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\StockBalance;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class EloquentStockBalanceRepository implements StockBalanceRepositoryPort
{
    public function getBalanceByVariantId(UuidInterface $variantId): StockBalance
    {
        $result = DB::selectOne(
            "SELECT COALESCE(SUM(
                CASE
                    WHEN sm.type = 'ENTRY' THEN sm.quantity
                    WHEN sm.type = 'EXIT'  THEN -sm.quantity
                    WHEN sm.type = 'REVERSAL' THEN
                        CASE WHEN orig.type = 'ENTRY' THEN -sm.quantity
                             ELSE sm.quantity END
                END
            ), 0) AS balance
            FROM stock_movements sm
            LEFT JOIN stock_movements orig ON sm.referenced_movement_id = orig.id
            WHERE sm.variant_id = ?",
            [$variantId->toString()]
        );

        return new StockBalance($variantId, (int) $result->balance);
    }
}
