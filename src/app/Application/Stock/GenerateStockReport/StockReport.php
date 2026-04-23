<?php

namespace App\Application\Stock\GenerateStockReport;

use DateTimeImmutable;

class StockReport
{
    /** @param StockReportEntry[] $entries */
    public function __construct(
        private DateTimeImmutable $startDate,
        private DateTimeImmutable $endDate,
        private DateTimeImmutable $generatedAt,
        private array             $entries,
    ) {}

    public function getStartDate(): DateTimeImmutable   { return $this->startDate; }
    public function getEndDate(): DateTimeImmutable     { return $this->endDate; }
    public function getGeneratedAt(): DateTimeImmutable { return $this->generatedAt; }

    /** @return StockReportEntry[] */
    public function getEntries(): array { return $this->entries; }
}
