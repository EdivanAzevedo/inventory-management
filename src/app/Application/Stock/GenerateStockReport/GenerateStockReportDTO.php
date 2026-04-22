<?php

namespace App\Application\Stock\GenerateStockReport;

class GenerateStockReportDTO
{
    public function __construct(
        public readonly string  $startDate,
        public readonly string  $endDate,
        public readonly ?string $productId   = null,
        public readonly ?string $productType = null,
    ) {}
}
