<?php

namespace App\Infrastructure\Http\Resources\Stock;

use App\Application\Stock\GenerateStockReport\StockReportEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockReportEntry */
class StockReportEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product' => [
                'id'   => $this->getProductId(),
                'name' => $this->getProductName(),
                'type' => $this->getProductType(),
            ],
            'variant' => [
                'id'    => $this->getVariantId(),
                'sku'   => $this->getSku(),
                'color' => $this->getColor(),
                'size'  => $this->getSize(),
                'unit'  => $this->getUnit(),
            ],
            'total_entries' => $this->getTotalEntries(),
            'total_exits'   => $this->getTotalExits(),
            'net_balance'   => $this->getNetBalance(),
        ];
    }
}
