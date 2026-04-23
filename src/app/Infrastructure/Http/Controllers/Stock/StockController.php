<?php

namespace App\Infrastructure\Http\Controllers\Stock;

use App\Application\Stock\CancelMovement\CancelMovementUseCase;
use App\Application\Stock\ListMovements\ListMovementsByVariantUseCase;
use App\Application\Stock\QueryStockBalance\QueryStockBalanceUseCase;
use App\Application\Stock\RecordEntry\RecordEntryDTO;
use App\Application\Stock\RecordEntry\RecordEntryUseCase;
use App\Application\Stock\RecordExit\RecordExitDTO;
use App\Application\Stock\RecordExit\RecordExitUseCase;
use App\Application\Stock\TransferStock\TransferStockDTO;
use App\Application\Stock\TransferStock\TransferStockUseCase;
use App\Infrastructure\Http\Requests\Stock\CancelMovementRequest;
use App\Infrastructure\Http\Requests\Stock\RecordEntryRequest;
use App\Infrastructure\Http\Requests\Stock\RecordExitRequest;
use App\Infrastructure\Http\Requests\Stock\TransferStockRequest;
use App\Infrastructure\Http\Resources\Stock\StockBalanceResource;
use App\Infrastructure\Http\Resources\Stock\StockMovementResource;
use App\Infrastructure\Http\Resources\Stock\TransferStockResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class StockController extends Controller
{
    public function __construct(
        private RecordEntryUseCase              $recordEntry,
        private RecordExitUseCase               $recordExit,
        private CancelMovementUseCase           $cancelMovement,
        private QueryStockBalanceUseCase        $queryBalance,
        private ListMovementsByVariantUseCase   $listMovements,
        private TransferStockUseCase            $transferStock,
    ) {}

    public function entry(RecordEntryRequest $request): JsonResponse
    {
        $movement = $this->recordEntry->execute(new RecordEntryDTO(
            variantId: $request->input('variant_id'),
            quantity:  $request->input('quantity'),
            reason:    $request->input('reason'),
        ));

        return (new StockMovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }

    public function exit(RecordExitRequest $request): JsonResponse
    {
        $movement = $this->recordExit->execute(new RecordExitDTO(
            variantId: $request->input('variant_id'),
            quantity:  $request->input('quantity'),
            reason:    $request->input('reason'),
        ));

        return (new StockMovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }

    public function cancel(CancelMovementRequest $request, string $id): JsonResponse
    {
        $reversal = $this->cancelMovement->execute($id, $request->input('reason'));

        return (new StockMovementResource($reversal))
            ->response()
            ->setStatusCode(201);
    }

    public function balance(string $variantId): StockBalanceResource
    {
        return new StockBalanceResource($this->queryBalance->execute($variantId));
    }

    public function movements(string $variantId): AnonymousResourceCollection
    {
        return StockMovementResource::collection($this->listMovements->execute($variantId));
    }

    public function transfer(TransferStockRequest $request): JsonResponse
    {
        $result = $this->transferStock->execute(new TransferStockDTO(
            fromVariantId: $request->input('from_variant_id'),
            toVariantId:   $request->input('to_variant_id'),
            quantity:      $request->input('quantity'),
            reason:        $request->input('reason'),
        ));

        return (new TransferStockResource($result))
            ->response()
            ->setStatusCode(201);
    }
}
