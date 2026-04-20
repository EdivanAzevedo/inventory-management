<?php

namespace App\Infrastructure\Http\Resources\Stock;

use App\Domain\Stock\StockBalance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockBalance */
class StockBalanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'variant_id' => $this->getVariantId()->toString(),
            'quantity'   => $this->getQuantity(),
        ];
    }
}
