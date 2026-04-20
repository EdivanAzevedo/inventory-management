<?php

namespace App\Application\Product\AddProductVariant;

use App\Domain\Product\Ports\ProductRepositoryPort;
use App\Domain\Product\ProductVariant;
use App\Domain\Shared\Ports\IdGeneratorPort;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class AddProductVariantUseCase
{
    public function __construct(
        private ProductRepositoryPort $repository,
        private IdGeneratorPort       $ids,
    ) {}

    public function execute(AddProductVariantDTO $dto): ProductVariant
    {
        $product = $this->repository->findById(Uuid::fromString($dto->productId));

        if ($product === null) {
            throw new RuntimeException("Produto não encontrado: {$dto->productId}");
        }

        $variant = new ProductVariant(
            id:           $this->ids->generate(),
            productId:    $product->getId(),
            sku:          $dto->sku,
            unit:         $dto->unit,
            minimumStock: $dto->minimumStock,
            color:        $dto->color,
            size:         $dto->size,
        );

        $product->addVariant($variant);

        $this->repository->save($product);

        return $variant;
    }
}
