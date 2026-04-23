<?php

namespace App\Infrastructure\Http\Resources\Stock;

use App\Application\Stock\TransferStock\TransferStockResult;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TransferStockResult */
class TransferStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'exit'  => new StockMovementResource($this->exit),
            'entry' => new StockMovementResource($this->entry),
        ];
    }
}
