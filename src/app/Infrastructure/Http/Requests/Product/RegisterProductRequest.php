<?php

namespace App\Infrastructure\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class RegisterProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'                      => ['required', 'string', 'max:255'],
            'type'                      => ['required', 'in:PRODUTO_FINAL,MATERIA_PRIMA,INSUMO'],
            'description'               => ['nullable', 'string'],
            'variants'                  => ['required', 'array', 'min:1'],
            'variants.*.sku'            => ['required', 'string', 'max:100', 'distinct'],
            'variants.*.unit'           => ['required', 'string', 'max:20'],
            'variants.*.minimum_stock'  => ['required', 'integer', 'min:0'],
            'variants.*.color'          => ['nullable', 'string', 'max:50'],
            'variants.*.size'           => ['nullable', 'string', 'max:50'],
        ];
    }
}
