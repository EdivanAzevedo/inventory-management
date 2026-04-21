<?php

namespace App\Application\Product\GetProduct;

use App\Domain\Product\Exceptions\ProductNotFoundException;
use App\Domain\Product\Ports\ProductRepositoryPort;
use App\Domain\Product\Product;
use Ramsey\Uuid\Uuid;

class GetProductUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
    ) {}

    public function execute(string $id): Product
    {
        $product = $this->repository->findById(Uuid::fromString($id));

        if ($product === null) {
            throw new ProductNotFoundException($id);
        }

        return $product;
    }
}
