<?php

namespace App\Infrastructure\Http\Resources\Stock;

use App\Domain\Stock\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockMovement */
class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->getId()->toString(),
            'variant_id'              => $this->getVariantId()->toString(),
            'type'                    => $this->getType()->value,
            'quantity'                => $this->getQuantity(),
            'reason'                  => $this->getReason(),
            'referenced_movement_id'  => $this->getReferencedMovementId()?->toString(),
            'created_at'              => $this->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
