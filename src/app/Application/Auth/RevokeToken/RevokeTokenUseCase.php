<?php

namespace App\Application\Auth\RevokeToken;

use App\Domain\User\Ports\UserTokenPort;

class RevokeTokenUseCase
{
    public function __construct(
        private UserTokenPort $tokenPort,
    ) {}

    public function execute(int $userId): void
    {
        $this->tokenPort->revokeAllTokens($userId);
    }
}
