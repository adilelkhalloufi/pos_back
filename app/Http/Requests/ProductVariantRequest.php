<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:60',
            'size' => 'nullable|string|max:60',
            'material' => 'nullable|string|max:100',
            'weight_grams' => 'nullable|numeric|min:0',
            'gemstone' => 'nullable|string|max:100',
            'attributes' => 'nullable|array',
            'price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ];
    }
}
