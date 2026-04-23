<?php

namespace App\Infrastructure\Auth;

use App\Domain\User\Ports\UserTokenPort;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

class SanctumTokenAdapter implements UserTokenPort
{
    public function createToken(int $userId, string $tokenName): string
    {
        /** @var UserModel $model */
        $model = UserModel::findOrFail($userId);

        return $model->createToken($tokenName)->plainTextToken;
    }

    public function revokeAllTokens(int $userId): void
    {
        /** @var UserModel $model */
        $model = UserModel::findOrFail($userId);

        $model->tokens()->delete();
    }
}
