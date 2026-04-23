<?php

namespace App\Infrastructure\Auth;

use App\Domain\Shared\Ports\PasswordHasherPort;
use Illuminate\Support\Facades\Hash;

class BcryptPasswordHasher implements PasswordHasherPort
{
    public function hash(string $plain): string
    {
        return Hash::make($plain);
    }

    public function verify(string $plain, string $hash): bool
    {
        return Hash::check($plain, $hash);
    }
}
