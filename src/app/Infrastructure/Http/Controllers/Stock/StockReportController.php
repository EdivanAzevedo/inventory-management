<?php

namespace App\Infrastructure\Http\Controllers\Stock;

use App\Application\Stock\GenerateStockReport\GenerateStockReportDTO;
use App\Application\Stock\GenerateStockReport\GenerateStockReportUseCase;
use App\Infrastructure\Http\Requests\Stock\StockReportRequest;
use App\Infrastructure\Http\Resources\Stock\StockReportResource;
use Illuminate\Routing\Controller;

class StockReportController extends Controller
{
    public function __construct(
        private GenerateStockReportUseCase $generateReport,
    ) {}

    public function __invoke(StockReportRequest $request): StockReportResource
    {
        $report = $this->generateReport->execute(new GenerateStockReportDTO(
            startDate:   $request->input('start_date'),
            endDate:     $request->input('end_date'),
            productId:   $request->input('product_id'),
            productType: $request->input('product_type'),
        ));

        return new StockReportResource($report);
    }
}
