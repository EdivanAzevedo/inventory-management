<?php

namespace App\Application\Stock\GenerateStockReport;

use DateTimeImmutable;

interface StockReportRepositoryPort
{
    public function generate(
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate,
        ?string $productId   = null,
        ?string $productType = null,
    ): StockReport;
}
