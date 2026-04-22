<?php

namespace App\Infrastructure\Http\Requests\Stock;

use App\Domain\Product\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockReportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start_date'   => ['required', 'date_format:Y-m-d'],
            'end_date'     => ['required', 'date_format:Y-m-d', 'gte:start_date'],
            'product_id'   => ['nullable', 'uuid'],
            'product_type' => ['nullable', Rule::enum(ProductType::class)],
        ];
    }
}
