<?php

namespace App\Application\Stock\RecordEntry;

use App\Domain\Product\Exceptions\VariantNotFoundException;
use App\Domain\Product\Ports\ProductVariantRepositoryPort;
use App\Domain\Shared\Ports\IdGeneratorPort;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Domain\Stock\StockMovement;
use Illuminate\Contracts\Events\Dispatcher;
use Ramsey\Uuid\Uuid;

class RecordEntryUseCase
{
    public function __construct(
        private StockMovementRepositoryPort  $movements,
        private ProductVariantRepositoryPort $variants,
        private IdGeneratorPort              $ids,
        private Dispatcher                   $dispatcher,
    ) {}

    public function execute(RecordEntryDTO $dto): StockMovement
    {
        $variantId = Uuid::fromString($dto->variantId);

        if ($this->variants->findById($variantId) === null) {
            throw new VariantNotFoundException($dto->variantId);
        }

        $movement = StockMovement::createEntry(
            id:        $this->ids->generate(),
            variantId: $variantId,
            quantity:  $dto->quantity,
            reason:    $dto->reason,
        );

        $this->movements->save($movement);

        foreach ($movement->pullDomainEvents() as $event) {
            $this->dispatcher->dispatch($event);
        }

        return $movement;
    }
}
