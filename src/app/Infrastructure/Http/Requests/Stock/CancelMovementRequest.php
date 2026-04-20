<?php

namespace App\Infrastructure\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class CancelMovementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
