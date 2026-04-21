<?php

namespace App\Application\Product\ListProducts;

use App\Domain\Product\Ports\ProductRepositoryPort;

class ListProductsUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
    ) {}

    /** @return \App\Domain\Product\Product[] */
    public function execute(): array
    {
        return $this->repository->findAll();
    }
}
