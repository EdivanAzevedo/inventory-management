<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Stock\Ports\StockReportRepositoryPort;
use App\Domain\Stock\StockReport;
use App\Domain\Stock\StockReportEntry;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

class EloquentStockReportRepository implements StockReportRepositoryPort
{
    public function generate(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $productId   = null,
        ?string $productType = null,
    ): StockReport {
        $start = $startDate->format('Y-m-d') . ' 00:00:00';
        $end   = $endDate->format('Y-m-d') . ' 23:59:59';

        $sql = "
            SELECT
                p.id            AS product_id,
                p.name          AS product_name,
                p.type          AS product_type,
                pv.id           AS variant_id,
                pv.sku,
                pv.color,
                pv.size,
                pv.unit,
                COALESCE(SUM(CASE WHEN sm.type = 'ENTRY' AND sm.created_at BETWEEN ? AND ? THEN sm.quantity ELSE 0 END), 0) AS total_entries,
                COALESCE(SUM(CASE WHEN sm.type = 'EXIT'  AND sm.created_at BETWEEN ? AND ? THEN sm.quantity ELSE 0 END), 0) AS total_exits,
                COALESCE(SUM(
                    CASE WHEN sm.created_at <= ? THEN
                        CASE
                            WHEN sm.type = 'ENTRY'    THEN  sm.quantity
                            WHEN sm.type = 'EXIT'     THEN -sm.quantity
                            WHEN sm.type = 'REVERSAL' THEN
                                CASE WHEN orig.type = 'ENTRY' THEN -sm.quantity ELSE sm.quantity END
                            ELSE 0
                        END
                    ELSE 0 END
                ), 0) AS net_balance
            FROM products p
            JOIN product_variants pv
                ON pv.product_id = p.id AND pv.deleted_at IS NULL
            LEFT JOIN stock_movements sm
                ON sm.variant_id = pv.id
            LEFT JOIN stock_movements orig
                ON sm.referenced_movement_id = orig.id
            WHERE p.deleted_at IS NULL
              AND pv.deleted_at IS NULL
              AND (? IS NULL OR p.id = ?)
              AND (? IS NULL OR p.type = ?)
            GROUP BY p.id, p.name, p.type, pv.id, pv.sku, pv.color, pv.size, pv.unit
            HAVING total_entries > 0 OR total_exits > 0 OR net_balance <> 0
            ORDER BY p.name, pv.sku
        ";

        $bindings = [
            $start, $end,
            $start, $end,
            $end,
            $productId, $productId,
            $productType, $productType,
        ];

        $rows = DB::select($sql, $bindings);

        $entries = array_map(
            fn (object $row) => new StockReportEntry(
                productId:    $row->product_id,
                productName:  $row->product_name,
                productType:  $row->product_type,
                variantId:    $row->variant_id,
                sku:          $row->sku,
                color:        $row->color,
                size:         $row->size,
                unit:         $row->unit,
                totalEntries: (int) $row->total_entries,
                totalExits:   (int) $row->total_exits,
                netBalance:   (int) $row->net_balance,
            ),
            $rows,
        );

        return new StockReport(
            startDate:   $startDate,
            endDate:     $endDate,
            generatedAt: new DateTimeImmutable(),
            entries:     $entries,
        );
    }
}
