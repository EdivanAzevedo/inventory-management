<?php

namespace App\Application\Product\UpdateProduct;

use App\Domain\Product\Ports\ProductRepositoryPort;
use App\Domain\Product\Product;
use App\Domain\Product\ProductType;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class UpdateProductUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
    ) {}

    public function execute(UpdateProductDTO $dto): Product
    {
        $product = $this->repository->findById(Uuid::fromString($dto->id));

        if ($product === null) {
            throw new RuntimeException("Produto não encontrado: {$dto->id}");
        }

        $product->update($dto->name, ProductType::from($dto->type), $dto->description);

        $this->repository->save($product);

        return $product;
    }
}
