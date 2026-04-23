<?php

namespace App\Application\Stock\Shared;

use App\Domain\Product\Ports\ProductVariantRepositoryPort;
use App\Domain\Shared\Ports\EventDispatcherPort;
use App\Domain\Stock\Events\StockBelowMinimumDetected;
use Ramsey\Uuid\UuidInterface;

final class MinimumStockChecker
{
    public function __construct(
        private ProductVariantRepositoryPort $variants,
        private EventDispatcherPort          $dispatcher,
    ) {}

    public function check(UuidInterface $variantId, int $newBalance): void
    {
        $variant = $this->variants->findById($variantId);

        if ($variant && $variant->isBelowMinimum($newBalance)) {
            $this->dispatcher->dispatch(new StockBelowMinimumDetected(
                variantId:      $variantId,
                currentBalance: $newBalance,
                minimumStock:   $variant->getMinimumStock(),
            ));
        }
    }
}
