<?php

namespace App\Domain\User\Exceptions;

use DomainException;

class InvalidCredentialsException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Credenciais inválidas.');
    }
}
