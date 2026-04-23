<?php

namespace App\Infrastructure\Http\Resources\Stock;

use App\Application\Stock\GenerateStockReport\StockReport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin StockReport */
class StockReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'period' => [
                'start' => $this->getStartDate()->format('Y-m-d'),
                'end'   => $this->getEndDate()->format('Y-m-d'),
            ],
            'generated_at' => $this->getGeneratedAt()->format('Y-m-d H:i:s'),
            'items'        => StockReportEntryResource::collection($this->getEntries()),
        ];
    }
}
