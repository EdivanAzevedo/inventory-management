<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Product\Ports\ProductRepositoryPort;
use App\Domain\Product\Product;
use App\Domain\Product\ProductType;
use App\Domain\Product\ProductVariant;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class EloquentProductRepository implements ProductRepositoryPort
{
    public function save(Product $product): void
    {
        $model = ProductModel::withTrashed()->firstOrNew(['id' => $product->getId()->toString()]);

        $model->fill([
            'name'        => $product->getName(),
            'type'        => $product->getType()->value,
            'description' => $product->getDescription(),
        ]);

        if (! $product->isActive()) {
            $model->deleted_at = now();
        }

        $model->save();

        foreach ($product->getVariants() as $variant) {
            $model->variants()->updateOrCreate(
                ['id' => $variant->getId()->toString()],
                [
                    'sku'           => $variant->getSku(),
                    'unit'          => $variant->getUnit(),
                    'minimum_stock' => $variant->getMinimumStock(),
                    'color'         => $variant->getColor(),
                    'size'          => $variant->getSize(),
                    'deleted_at'    => $variant->isActive() ? null : now(),
                ]
            );
        }
    }

    public function findById(UuidInterface $id): ?Product
    {
        $model = ProductModel::with('variants')->find($id->toString());

        return $model ? $this->toDomain($model) : null;
    }

    public function findAll(): array
    {
        return ProductModel::with('variants')
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(ProductModel $model): Product
    {
        $product = Product::reconstitute(
            id:          Uuid::fromString($model->id),
            name:        $model->name,
            type:        ProductType::from($model->type),
            description: $model->description,
            active:      $model->deleted_at === null,
        );

        foreach ($model->variants as $variantModel) {
            $product->addVariant(new ProductVariant(
                id:           Uuid::fromString($variantModel->id),
                productId:    Uuid::fromString($variantModel->product_id),
                sku:          $variantModel->sku,
                unit:         $variantModel->unit,
                minimumStock: $variantModel->minimum_stock,
                color:        $variantModel->color,
                size:         $variantModel->size,
                active:       $variantModel->deleted_at === null,
            ));
        }

        return $product;
    }
}
