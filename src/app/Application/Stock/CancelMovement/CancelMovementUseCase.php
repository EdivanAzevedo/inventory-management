<?php

namespace App\Application\Stock\CancelMovement;

use App\Domain\Shared\Ports\IdGeneratorPort;
use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Domain\Stock\StockMovement;
use Illuminate\Contracts\Events\Dispatcher;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class CancelMovementUseCase
{
    public function __construct(
        private StockMovementRepositoryPort $movements,
        private StockBalanceRepositoryPort  $balances,
        private IdGeneratorPort             $ids,
        private Dispatcher                  $dispatcher,
    ) {}

    public function execute(string $movementId, ?string $reason = null): StockMovement
    {
        $original = $this->movements->findById(Uuid::fromString($movementId));

        if (! $original) {
            throw new RuntimeException("Movimentação {$movementId} não encontrada.");
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

        return $reversal;
    }
}
