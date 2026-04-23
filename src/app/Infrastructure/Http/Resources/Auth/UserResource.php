<?php

namespace App\Infrastructure\Http\Resources\Auth;

use App\Domain\User\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->getId(),
            'name'  => $this->getName(),
            'email' => $this->getEmail(),
            'role'  => $this->getRole()->value,
        ];
    }
}
