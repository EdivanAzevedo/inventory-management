<?php

namespace App\Application\Product\ListInactiveProducts;

use App\Domain\Product\Ports\ProductRepositoryPort;

class ListInactiveProductsUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
    ) {}

    /** @return \App\Domain\Product\Product[] */
    public function execute(): array
    {
        return $this->repository->findInactive();
    }
}
