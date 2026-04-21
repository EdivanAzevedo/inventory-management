<?php

namespace App\Domain\Product\Exceptions;

use DomainException;

class VariantNotFoundException extends DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("Variante não encontrada: {$id}");
    }
}
