<?php

namespace App\Domain\Stock;

use App\Domain\Stock\Events\StockMovementRegistered;
use App\Domain\Stock\Events\StockMovementReversed;
use DateTimeImmutable;
use DomainException;
use Ramsey\Uuid\UuidInterface;

class StockMovement
{
    /** @var object[] */
    private array $domainEvents = [];

    public function __construct(
        private UuidInterface  $id,
        private UuidInterface  $variantId,
        private MovementType   $type,
        private int            $quantity,
        private ?string        $reason = null,
        private ?UuidInterface $referencedMovementId = null,
        private DateTimeImmutable $createdAt = new DateTimeImmutable(),
    ) {}

    public static function createEntry(
        UuidInterface $id,
        UuidInterface $variantId,
        int $quantity,
        ?string $reason = null,
    ): self {
        $movement = new self($id, $variantId, MovementType::ENTRY, $quantity, $reason);
        $movement->domainEvents[] = new StockMovementRegistered($id, $variantId);

        return $movement;
    }

    public static function createExit(
        UuidInterface $id,
        UuidInterface $variantId,
        int $quantity,
        int $currentBalance,
        ?string $reason = null,
    ): self {
        if ($currentBalance < $quantity) {
            throw new DomainException(
                "Saldo insuficiente: disponível {$currentBalance}, solicitado {$quantity}."
            );
        }

        $movement = new self($id, $variantId, MovementType::EXIT, $quantity, $reason);
        $movement->domainEvents[] = new StockMovementRegistered($id, $variantId);

        return $movement;
    }

    public static function createReversal(
        UuidInterface $id,
        StockMovement $original,
        int $currentBalance,
        ?string $reason = null,
    ): self {
        if ($original->getType() === MovementType::REVERSAL) {
            throw new DomainException('Não é possível estornar um estorno.');
        }

        // Reversing an ENTRY reduces stock; validate balance won't go negative.
        if ($original->getType() === MovementType::ENTRY && $currentBalance < $original->quantity) {
            throw new DomainException(
                "Saldo insuficiente para estornar entrada: disponível {$currentBalance}, necessário {$original->quantity}."
            );
        }

        $movement = new self(
            id:                   $id,
            variantId:            $original->variantId,
            type:                 MovementType::REVERSAL,
            quantity:             $original->quantity,
            reason:               $reason,
            referencedMovementId: $original->id,
        );

        $movement->domainEvents[] = new StockMovementReversed($id, $original->id, $original->variantId);

        return $movement;
    }

    public static function reconstitute(
        UuidInterface $id,
        UuidInterface $variantId,
        MovementType $type,
        int $quantity,
        ?string $reason,
        ?UuidInterface $referencedMovementId,
        DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $variantId, $type, $quantity, $reason, $referencedMovementId, $createdAt);
    }

    public function getId(): UuidInterface               { return $this->id; }
    public function getVariantId(): UuidInterface        { return $this->variantId; }
    public function getType(): MovementType              { return $this->type; }
    public function getQuantity(): int                   { return $this->quantity; }
    public function getReason(): ?string                 { return $this->reason; }
    public function getReferencedMovementId(): ?UuidInterface { return $this->referencedMovementId; }
    public function getCreatedAt(): DateTimeImmutable    { return $this->createdAt; }

    /** @return object[] */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }
}
