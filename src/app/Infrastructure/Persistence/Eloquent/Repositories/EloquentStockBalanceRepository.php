<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Stock\MovementType;
use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\StockBalance;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

class EloquentStockBalanceRepository implements StockBalanceRepositoryPort
{
    public function getBalanceByVariantId(UuidInterface $variantId): StockBalance
    {
        return $this->calculateBalance($variantId);
    }

    public function getBalanceByVariantIdForUpdate(UuidInterface $variantId): StockBalance
    {
        // Bloqueia o registro da variante para serializar operações concorrentes.
        // Deve ser chamado dentro de DB::transaction().
        DB::table('product_variants')
            ->where('id', $variantId->toString())
            ->lockForUpdate()
            ->first();

        return $this->calculateBalance($variantId);
    }

    private function calculateBalance(UuidInterface $variantId): StockBalance
    {
        $entry    = MovementType::ENTRY->value;
        $exit     = MovementType::EXIT->value;
        $reversal = MovementType::REVERSAL->value;

        $result = DB::selectOne(
            "SELECT COALESCE(SUM(
                CASE
                    WHEN sm.type = '$entry'    THEN  sm.quantity
                    WHEN sm.type = '$exit'     THEN -sm.quantity
                    WHEN sm.type = '$reversal' THEN
                        CASE WHEN orig.type = '$entry' THEN -sm.quantity
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
