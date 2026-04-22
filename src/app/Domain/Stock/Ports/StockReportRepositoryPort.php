<?php

namespace App\Domain\Stock\Ports;

use App\Domain\Stock\StockReport;
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
