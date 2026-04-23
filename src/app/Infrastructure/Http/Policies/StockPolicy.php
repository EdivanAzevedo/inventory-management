<?php

namespace App\Infrastructure\Http\Policies;

use App\Domain\User\UserRole;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

class StockPolicy
{
    public function record(UserModel $user): bool
    {
        return $this->hasRole($user, UserRole::admin, UserRole::operator);
    }

    public function cancel(UserModel $user): bool
    {
        return $this->hasRole($user, UserRole::admin, UserRole::operator);
    }

    public function transfer(UserModel $user): bool
    {
        return $this->hasRole($user, UserRole::admin, UserRole::operator);
    }

    public function viewBalance(UserModel $user): bool
    {
        return true;
    }

    public function viewMovements(UserModel $user): bool
    {
        return true;
    }

    public function viewReport(UserModel $user): bool
    {
        return true;
    }

    private function hasRole(UserModel $user, UserRole ...$allowed): bool
    {
        return in_array(UserRole::from($user->role), $allowed, strict: true);
    }
}
