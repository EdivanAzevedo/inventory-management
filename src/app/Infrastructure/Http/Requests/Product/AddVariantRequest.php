<?php

namespace App\Infrastructure\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class AddVariantRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'sku'           => ['required', 'string', 'max:100'],
            'unit'          => ['required', 'string', 'max:20'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'color'         => ['nullable', 'string', 'max:50'],
            'size'          => ['nullable', 'string', 'max:50'],
        ];
    }
}
