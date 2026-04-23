<?php

namespace App\Infrastructure\Http\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class TransferStockRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'from_variant_id' => ['required', 'uuid'],
            'to_variant_id'   => ['required', 'uuid', 'different:from_variant_id'],
            'quantity'        => ['required', 'integer', 'min:1'],
            'reason'          => ['nullable', 'string', 'max:255'],
        ];
    }
}
