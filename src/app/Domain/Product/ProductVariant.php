<?php

namespace App\Domain\Product;

use Ramsey\Uuid\UuidInterface;

class ProductVariant
{
    public function __construct(
        private UuidInterface $id,
        private UuidInterface $productId,
        private string $sku,
        private string $unit,
        private int $minimumStock,
        private ?string $color = null,
        private ?string $size = null,
        private bool $active = true,
    ) {}

    public function getId(): UuidInterface       { return $this->id; }
    public function getProductId(): UuidInterface { return $this->productId; }
    public function getSku(): string              { return $this->sku; }
    public function getUnit(): string             { return $this->unit; }
    public function getMinimumStock(): int        { return $this->minimumStock; }
    public function getColor(): ?string           { return $this->color; }
    public function getSize(): ?string            { return $this->size; }
    public function isActive(): bool              { return $this->active; }

    public function deactivate(): void { $this->active = false; }
}
