<?php

namespace App\Application\Stock\RecordEntry;

class RecordEntryDTO
{
    public function __construct(
        public readonly string $variantId,
        public readonly int    $quantity,
        public readonly ?string $reason = null,
    ) {}
}
