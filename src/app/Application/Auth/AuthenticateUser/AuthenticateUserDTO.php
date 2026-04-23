<?php

namespace App\Application\Auth\AuthenticateUser;

final class AuthenticateUserDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
