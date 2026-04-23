<?php

namespace App\Application\Stock\TransferStock;

use App\Application\Stock\Shared\MinimumStockChecker;
use App\Domain\Product\Exceptions\VariantNotFoundException;
use App\Domain\Product\Ports\ProductVariantRepositoryPort;
use App\Domain\Shared\Ports\ClockPort;
use App\Domain\Shared\Ports\EventDispatcherPort;
use App\Domain\Shared\Ports\IdGeneratorPort;
use App\Domain\Shared\Ports\TransactionPort;
use App\Domain\Stock\Ports\StockBalanceRepositoryPort;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Domain\Stock\StockMovement;
use Ramsey\Uuid\Uuid;

class TransferStockUseCase
{
    public function __construct(
        private StockMovementRepositoryPort  $movements,
        private StockBalanceRepositoryPort   $balances,
        private ProductVariantRepositoryPort $variants,
        private IdGeneratorPort              $ids,
        private EventDispatcherPort          $dispatcher,
        private MinimumStockChecker          $minimumStockChecker,
        private TransactionPort              $transaction,
        private ClockPort                    $clock,
    ) {}

    public function execute(TransferStockDTO $dto): TransferStockResult
    {
        $fromId = Uuid::fromString($dto->fromVariantId);
        $toId   = Uuid::fromString($dto->toVariantId);

        if ($this->variants->findById($fromId) === null) {
            throw new VariantNotFoundException($dto->fromVariantId);
        }

        if ($this->variants->findById($toId) === null) {
            throw new VariantNotFoundException($dto->toVariantId);
        }

        [$exit, $entry, $newSourceBalance] = $this->transaction->run(function () use ($fromId, $toId, $dto) {
            $balance = $this->balances->getBalanceByVariantIdForUpdate($fromId);

            $exit = StockMovement::createExit(
                id:             $this->ids->generate(),
                variantId:      $fromId,
                quantity:       $dto->quantity,
                currentBalance: $balance->getQuantity(),
                createdAt:      $this->clock->now(),
                reason:         $dto->reason,
            );

            $entry = StockMovement::createEntry(
                id:        $this->ids->generate(),
                variantId: $toId,
                quantity:  $dto->quantity,
                createdAt: $this->clock->now(),
                reason:    $dto->reason,
            );

            $this->movements->save($exit);
            $this->movements->save($entry);

            return [$exit, $entry, $balance->getQuantity() - $dto->quantity];
        });

        foreach ($exit->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }

        foreach ($entry->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }

        $this->minimumStockChecker->check($fromId, $newSourceBalance);

        return new TransferStockResult($exit, $entry);
    }
}
