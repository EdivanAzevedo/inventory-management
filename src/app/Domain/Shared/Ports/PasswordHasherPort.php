<?php

namespace App\Domain\Shared\Ports;

interface PasswordHasherPort
{
    public function hash(string $plain): string;

    public function verify(string $plain, string $hash): bool;
}
