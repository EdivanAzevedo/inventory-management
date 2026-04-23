<?php

namespace App\Domain\User\Ports;

interface UserTokenPort
{
    public function createToken(int $userId, string $tokenName): string;

    public function revokeAllTokens(int $userId): void;
}
