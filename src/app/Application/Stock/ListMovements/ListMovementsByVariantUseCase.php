<?php

namespace App\Application\Stock\ListMovements;

use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use Ramsey\Uuid\Uuid;

class ListMovementsByVariantUseCase
{
    public function __construct(
        private StockMovementRepositoryPort $movements,
    ) {}

    public function execute(string $variantId): array
    {
        return $this->movements->findByVariantId(Uuid::fromString($variantId));
    }
}
