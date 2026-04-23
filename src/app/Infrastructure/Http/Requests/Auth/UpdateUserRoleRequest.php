<?php

namespace App\Infrastructure\Http\Requests\Auth;

use App\Domain\User\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'role' => ['required', new Enum(UserRole::class)],
        ];
    }
}
