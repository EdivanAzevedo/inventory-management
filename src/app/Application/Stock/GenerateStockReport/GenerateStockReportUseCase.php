<?php

namespace App\Application\Stock\GenerateStockReport;

use App\Domain\Stock\Ports\StockReportRepositoryPort;
use App\Domain\Stock\StockReport;
use DateTimeImmutable;
use InvalidArgumentException;

class GenerateStockReportUseCase
{
    public function __construct(
        private StockReportRepositoryPort $reports,
    ) {}

    public function execute(GenerateStockReportDTO $dto): StockReport
    {
        $startDate = DateTimeImmutable::createFromFormat('Y-m-d', $dto->startDate);
        $endDate   = DateTimeImmutable::createFromFormat('Y-m-d', $dto->endDate);

        if ($startDate === false || $endDate === false) {
            throw new InvalidArgumentException('Formato de data inválido. Use Y-m-d.');
        }

        if ($startDate > $endDate) {
            throw new InvalidArgumentException('A data inicial não pode ser maior que a data final.');
        }

        return $this->reports->generate(
            startDate:   $startDate,
            endDate:     $endDate,
            productId:   $dto->productId,
            productType: $dto->productType,
        );
    }
}
