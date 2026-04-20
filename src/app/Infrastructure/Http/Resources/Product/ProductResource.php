<?php

namespace App\Infrastructure\Http\Resources\Product;

use App\Domain\Product\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->getId()->toString(),
            'name'        => $this->getName(),
            'type'        => $this->getType()->value,
            'description' => $this->getDescription(),
            'active'      => $this->isActive(),
            'variants'    => ProductVariantResource::collection($this->getVariants()),
        ];
    }
}
