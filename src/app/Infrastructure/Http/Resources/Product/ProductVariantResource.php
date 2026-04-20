<?php

namespace App\Infrastructure\Http\Resources\Product;

use App\Domain\Product\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductVariant */
class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->getId()->toString(),
            'sku'           => $this->getSku(),
            'unit'          => $this->getUnit(),
            'minimum_stock' => $this->getMinimumStock(),
            'color'         => $this->getColor(),
            'size'          => $this->getSize(),
            'active'        => $this->isActive(),
        ];
    }
}
