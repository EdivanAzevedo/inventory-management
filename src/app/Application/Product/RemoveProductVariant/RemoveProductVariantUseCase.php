<?php

namespace App\Application\Product\RemoveProductVariant;

use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Ports\ProductRepositoryPort;
use Ramsey\Uuid\Uuid;

class RemoveProductVariantUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
    ) {}

    public function execute(string $productId, string $variantId): void
    {
        $product = $this->repository->findById(Uuid::fromString($productId));

        if ($product === null) {
            throw new ProductNotFoundException($productId);
        }

        $product->removeVariant(Uuid::fromString($variantId));

        $this->repository->save($product);
    }
}
