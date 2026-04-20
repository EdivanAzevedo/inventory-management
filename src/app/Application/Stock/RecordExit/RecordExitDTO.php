<?php

namespace App\Application\Stock\RecordExit;

class RecordExitDTO
{
    public function __construct(
        public readonly string  $variantId,
        public readonly int     $quantity,
        public readonly ?string $reason = null,
    ) {}
}
