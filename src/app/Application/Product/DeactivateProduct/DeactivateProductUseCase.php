<?php

namespace App\Application\Product\DeactivateProduct;

use App\Domain\Product\Ports\ProductRepositoryPort;
use Illuminate\Contracts\Events\Dispatcher;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class DeactivateProductUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
        private Dispatcher $events,
    ) {}

    public function execute(string $id): void
    {
        $product = $this->repository->findById(Uuid::fromString($id));

        if ($product === null) {
            throw new RuntimeException("Produto não encontrado: {$id}");
        }

        $product->deactivate();

        $this->repository->save($product);

        foreach ($product->pullDomainEvents() as $event) {
            $this->events->dispatch($event);
        }
    }
}
