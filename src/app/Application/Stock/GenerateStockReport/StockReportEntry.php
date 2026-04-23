<?php

namespace App\Application\Stock\GenerateStockReport;

class StockReportEntry
{
    public function __construct(
        private string  $productId,
        private string  $productName,
        private string  $productType,
        private string  $variantId,
        private string  $sku,
        private ?string $color,
        private ?string $size,
        private string  $unit,
        private int     $totalEntries,
        private int     $totalExits,
        private int     $netBalance,
    ) {}

    public function getProductId(): string   { return $this->productId; }
    public function getProductName(): string  { return $this->productName; }
    public function getProductType(): string  { return $this->productType; }
    public function getVariantId(): string    { return $this->variantId; }
    public function getSku(): string          { return $this->sku; }
    public function getColor(): ?string       { return $this->color; }
    public function getSize(): ?string        { return $this->size; }
    public function getUnit(): string         { return $this->unit; }
    public function getTotalEntries(): int    { return $this->totalEntries; }
    public function getTotalExits(): int      { return $this->totalExits; }
    public function getNetBalance(): int      { return $this->netBalance; }
}
