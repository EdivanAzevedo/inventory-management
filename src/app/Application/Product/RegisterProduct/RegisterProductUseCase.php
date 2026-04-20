<?php

namespace App\Application\Product\RegisterProduct;

use App\Domain\Product\Ports\ProductRepositoryPort;
use App\Domain\Product\Product;
use App\Domain\Product\ProductType;
use App\Domain\Product\ProductVariant;
use App\Domain\Shared\Ports\IdGeneratorPort;

class RegisterProductUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
        private IdGeneratorPort       $ids,
    ) {}

    public function execute(RegisterProductDTO $dto): Product
    {
        $product = Product::create(
            id:          $this->ids->generate(),
            name:        $dto->name,
            type:        ProductType::from($dto->type),
            description: $dto->description,
        );

        foreach ($dto->variants as $variantDto) {
            $product->addVariant(new ProductVariant(
                id:           $this->ids->generate(),
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
