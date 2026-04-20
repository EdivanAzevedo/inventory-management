<?php

namespace App\Infrastructure\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'type'        => ['required', 'in:PRODUTO_FINAL,MATERIA_PRIMA,INSUMO'],
            'description' => ['nullable', 'string'],
        ];
    }
}
