<?php

namespace App\Domain\Stock\Exceptions;

use DomainException;

class MovementAlreadyReversedException extends DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("Movimentação já foi estornada: {$id}");
    }
}
