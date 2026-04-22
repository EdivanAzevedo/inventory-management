<?php

namespace App\Application\Stock\RecordExit;

use App\Application\Stock\Shared\MinimumStockChecker;
use App\Domain\Product\Exceptions\VariantNotFoundException;
use App\Domain\Product\Ports\ProductVariantRepositoryPort;
use App\Domain\Shared\Ports\EventDispatcherPort;
use App\Domain\Shared\Ports\IdGeneratorPort;
use App\Domain\Shared\Ports\TransactionPort;
use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Domain\Stock\StockMovement;
use Ramsey\Uuid\Uuid;

class RecordExitUseCase
{
    public function __construct(
        private StockMovementRepositoryPort  $movements,
        private StockBalanceRepositoryPort   $balances,
        private ProductVariantRepositoryPort $variants,
        private IdGeneratorPort              $ids,
        private EventDispatcherPort          $dispatcher,
        private MinimumStockChecker          $minimumStockChecker,
        private TransactionPort              $transaction,
    ) {}

    public function execute(RecordExitDTO $dto): StockMovement
    {
        $variantId = Uuid::fromString($dto->variantId);

        if ($this->variants->findById($variantId) === null) {
            throw new VariantNotFoundException($dto->variantId);
        }

        [$movement, $newBalance] = $this->transaction->run(function () use ($variantId, $dto) {
            $balance  = $this->balances->getBalanceByVariantIdForUpdate($variantId);
            $movement = StockMovement::createExit(
                id:             $this->ids->generate(),
                variantId:      $variantId,
                quantity:       $dto->quantity,
                currentBalance: $balance->getQuantity(),
                reason:         $dto->reason,
            );

            $this->movements->save($movement);

            return [$movement, $balance->getQuantity() - $dto->quantity];
        });

        foreach ($movement->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }

        $this->minimumStockChecker->check($variantId, $newBalance);

        return $movement;
    }
}
