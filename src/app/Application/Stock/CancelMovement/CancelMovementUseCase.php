<?php

namespace App\Application\Stock\CancelMovement;

use App\Domain\Product\Ports\ProductVariantRepositoryPort;
use App\Domain\Shared\Ports\IdGeneratorPort;
use App\Domain\Stock\Events\StockBelowMinimumDetected;
use App\Domain\Stock\Exceptions\StockMovementNotFoundException;
use App\Domain\Stock\MovementType;
use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Domain\Stock\StockMovement;
use Illuminate\Contracts\Events\Dispatcher;
use Ramsey\Uuid\Uuid;

class CancelMovementUseCase
{
    public function __construct(
        private StockMovementRepositoryPort  $movements,
        private StockBalanceRepositoryPort   $balances,
        private ProductVariantRepositoryPort $variants,
        private IdGeneratorPort              $ids,
        private Dispatcher                   $dispatcher,
    ) {}

    public function execute(string $movementId, ?string $reason = null): StockMovement
    {
        $original = $this->movements->findById(Uuid::fromString($movementId));

        if (! $original) {
            throw new StockMovementNotFoundException($movementId);
        }

        $balance  = $this->balances->getBalanceByVariantId($original->getVariantId());
        $reversal = StockMovement::createReversal(
            id:             $this->ids->generate(),
            original:       $original,
            currentBalance: $balance->getQuantity(),
            reason:         $reason,
        );

        $this->movements->save($reversal);

        foreach ($reversal->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }

        if ($original->getType() === MovementType::ENTRY) {
            $newBalance = $balance->getQuantity() - $original->getQuantity();
            $this->checkMinimumStock($original->getVariantId(), $newBalance);
        }

        return $reversal;
    }

    private function checkMinimumStock(\Ramsey\Uuid\UuidInterface $variantId, int $newBalance): void
    {
        $variant = $this->variants->findById($variantId);

        if ($variant && $newBalance < $variant->getMinimumStock()) {
            $this->dispatcher->dispatch(new StockBelowMinimumDetected(
                variantId:      $variantId,
                currentBalance: $newBalance,
                minimumStock:   $variant->getMinimumStock(),
            ));
        }
    }
}
