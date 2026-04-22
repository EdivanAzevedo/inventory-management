<?php

namespace App\Application\Stock\CancelMovement;

use App\Application\Stock\Shared\MinimumStockChecker;
use App\Domain\Shared\Ports\EventDispatcherPort;
use App\Domain\Shared\Ports\IdGeneratorPort;
use App\Domain\Shared\Ports\TransactionPort;
use App\Domain\Stock\Exceptions\MovementAlreadyReversedException;
use App\Domain\Stock\Exceptions\StockMovementNotFoundException;
use App\Domain\Stock\MovementType;
use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Domain\Stock\StockMovement;
use Ramsey\Uuid\Uuid;

class CancelMovementUseCase
{
    public function __construct(
        private StockMovementRepositoryPort $movements,
        private StockBalanceRepositoryPort  $balances,
        private IdGeneratorPort             $ids,
        private EventDispatcherPort         $dispatcher,
        private MinimumStockChecker         $minimumStockChecker,
        private TransactionPort             $transaction,
    ) {}

    public function execute(string $movementId, ?string $reason = null): StockMovement
    {
        $original = $this->movements->findById(Uuid::fromString($movementId));

        if (! $original) {
            throw new StockMovementNotFoundException($movementId);
        }

        [$reversal, $newBalanceIfEntry] = $this->transaction->run(function () use ($original, $reason) {
            if ($this->movements->existsReversalFor($original->getId())) {
                throw new MovementAlreadyReversedException($original->getId()->toString());
            }

            $balance  = $this->balances->getBalanceByVariantIdForUpdate($original->getVariantId());
            $reversal = StockMovement::createReversal(
                id:             $this->ids->generate(),
                original:       $original,
                currentBalance: $balance->getQuantity(),
                reason:         $reason,
            );

            $this->movements->save($reversal);

            $newBalanceIfEntry = $original->getType() === MovementType::ENTRY
                ? $balance->getQuantity() - $original->getQuantity()
                : null;

            return [$reversal, $newBalanceIfEntry];
        });

        foreach ($reversal->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }

        if ($newBalanceIfEntry !== null) {
            $this->minimumStockChecker->check($original->getVariantId(), $newBalanceIfEntry);
        }

        return $reversal;
    }
}
