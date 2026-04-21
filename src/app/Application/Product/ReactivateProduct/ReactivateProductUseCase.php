<?php

namespace App\Application\Product\ReactivateProduct;

use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Ports\ProductRepositoryPort;
use Illuminate\Contracts\Events\Dispatcher;
use Ramsey\Uuid\Uuid;

class ReactivateProductUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
        private Dispatcher $events,
    ) {}

    public function execute(string $id): void
    {
        $product = $this->repository->findById(Uuid::fromString($id));

        if ($product === null) {
            throw new ProductNotFoundException($id);
        }

        $product->reactivate();

        $this->repository->save($product);

        foreach ($product->pullDomainEvents() as $event) {
            $this->events->dispatch($event);
        }
    }
}
