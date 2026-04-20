<?php

namespace App\Application\Product\RegisterProduct;

use App\Domain\Product\Ports\ProductRepositoryPort;
use App\Domain\Product\Product;
use App\Domain\Product\ProductType;
use App\Domain\Product\ProductVariant;
use Ramsey\Uuid\Uuid;

class RegisterProductUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
    ) {}

    public function execute(RegisterProductDTO $dto): Product
    {
        $product = Product::create(
            id:          Uuid::uuid4(),
            name:        $dto->name,
            type:        ProductType::from($dto->type),
            description: $dto->description,
        );

        foreach ($dto->variants as $variantDto) {
            $product->addVariant(new ProductVariant(
                id:           Uuid::uuid4(),
                productId:    $product->getId(),
                sku:          $variantDto->sku,
                unit:         $variantDto->unit,
                minimumStock: $variantDto->minimumStock,
                color:        $variantDto->color,
                size:         $variantDto->size,
            ));
        }

        $this->repository->save($product);

        return $product;
    }
}
