<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Product\Ports\ProductVariantRepositoryPort;
use App\Domain\Product\ProductVariant;
use App\Infrastructure\Persistence\Eloquent\Models\ProductVariantModel;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class EloquentProductVariantRepository implements ProductVariantRepositoryPort
{
    public function findById(UuidInterface $id): ?ProductVariant
    {
        $model = ProductVariantModel::find($id->toString());

        if (! $model) {
            return null;
        }

        return new ProductVariant(
            id:           Uuid::fromString($model->id),
            productId:    Uuid::fromString($model->product_id),
            sku:          $model->sku,
            unit:         $model->unit,
            minimumStock: $model->minimum_stock,
            color:        $model->color,
            size:         $model->size,
            active:       $model->deleted_at === null,
        );
    }
}
