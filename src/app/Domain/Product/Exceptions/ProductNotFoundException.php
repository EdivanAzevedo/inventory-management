<?php

namespace App\Domain\Product\Exceptions;

use DomainException;

class ProductNotFoundException extends DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("Produto não encontrado: {$id}");
    }
}
