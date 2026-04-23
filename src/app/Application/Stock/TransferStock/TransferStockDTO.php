<?php

namespace App\Application\Stock\TransferStock;

class TransferStockDTO
{
    public function __construct(
        public readonly string  $fromVariantId,
        public readonly string  $toVariantId,
        public readonly int     $quantity,
        public readonly ?string $reason = null,
    ) {}
}
