<?php

namespace App\Infrastructure\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class RecordEntryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'variant_id' => ['required', 'uuid'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'reason'     => ['nullable', 'string', 'max:255'],
        ];
    }
}
