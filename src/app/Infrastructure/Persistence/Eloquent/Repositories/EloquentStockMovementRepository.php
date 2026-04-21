<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Stock\MovementType;
use App\Domain\Stock\Ports\StockMovementRepositoryPort;
use App\Domain\Stock\StockMovement;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovementModel;
use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class EloquentStockMovementRepository implements StockMovementRepositoryPort
{
    public function save(StockMovement $movement): void
    {
        StockMovementModel::firstOrCreate(
            ['id' => $movement->getId()->toString()],
            [
                'variant_id'             => $movement->getVariantId()->toString(),
                'type'                   => $movement->getType()->value,
                'quantity'               => $movement->getQuantity(),
                'reason'                 => $movement->getReason(),
                'referenced_movement_id' => $movement->getReferencedMovementId()?->toString(),
            ]
        );
    }

    public function findById(UuidInterface $id): ?StockMovement
    {
        $model = StockMovementModel::find($id->toString());

        return $model ? $this->toDomain($model) : null;
    }

    public function findByVariantId(UuidInterface $variantId): array
    {
        return StockMovementModel::where('variant_id', $variantId->toString())
            ->orderBy('created_at')
            ->get()
            ->map(fn ($m) => $this->toDomain($m))
            ->all();
    }

    private function toDomain(StockMovementModel $model): StockMovement
    {
        return StockMovement::reconstitute(
            id:                   Uuid::fromString($model->id),
            variantId:            Uuid::fromString($model->variant_id),
            type:                 MovementType::from($model->type),
            quantity:             $model->quantity,
            reason:               $model->reason,
            referencedMovementId: $model->referenced_movement_id
                ? Uuid::fromString($model->referenced_movement_id)
                : null,
            createdAt: DateTimeImmutable::createFromInterface($model->created_at),
        );
    }
}
