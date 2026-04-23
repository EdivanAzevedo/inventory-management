<?php

namespace App\Domain\User\Exceptions;

use DomainException;

class UserAlreadyExistsException extends DomainException
{
    public function __construct(string $email)
    {
        parent::__construct("Já existe um usuário com o e-mail: {$email}");
    }
}
