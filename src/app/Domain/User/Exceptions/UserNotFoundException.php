<?php

namespace App\Domain\User\Exceptions;

use DomainException;

class UserNotFoundException extends DomainException
{
    public function __construct(int $id)
    {
        parent::__construct("Usuário não encontrado: {$id}");
    }
}
