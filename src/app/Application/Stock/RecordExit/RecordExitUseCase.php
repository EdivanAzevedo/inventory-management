<?php

namespace App\Application\Stock\RecordExit;

use App\Domain\Product\Ports\ProductVariantRepositoryPort;
use App\Domain\Shared\Ports\IdGeneratorPort;
use App\Domain\Stock\Events\StockBelowMinimumDetected;
use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Domain\Stock\StockMovement;
use Illuminate\Contracts\Events\Dispatcher;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class RecordExitUseCase
{
    public function __construct(
        private StockMovementRepositoryPort  $movements,
        private StockBalanceRepositoryPort   $balances,
        private ProductVariantRepositoryPort $variants,
        private IdGeneratorPort              $ids,
        private Dispatcher                   $dispatcher,
    ) {}

    public function execute(RecordExitDTO $dto): StockMovement
    {
        $variantId = Uuid::fromString($dto->variantId);

        $balance  = $this->balances->getBalanceByVariantId($variantId);
        $movement = StockMovement::createExit(
            id:             $this->ids->generate(),
            variantId:      $variantId,
            quantity:       $dto->quantity,
            currentBalance: $balance->getQuantity(),
            reason:         $dto->reason,
        );

        $this->movements->save($movement);

        foreach ($movement->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }

        $this->checkMinimumStock($variantId, $balance->getQuantity() - $dto->quantity);

        return $movement;
    }

    private function checkMinimumStock(UuidInterface $variantId, int $newBalance): void
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
