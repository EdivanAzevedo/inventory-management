<?php

namespace App\Domain\Stock\Exceptions;

use DomainException;

class StockMovementNotFoundException extends DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("Movimentação não encontrada: {$id}");
    }
}
