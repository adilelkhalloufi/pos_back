<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductComponentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'component_id' => 'required|integer|exists:products,id|different:product_id',
            'quantity'     => 'required|numeric|min:0.0001',
            'unit_id'      => 'nullable|integer|exists:units,id',
            'note'         => 'nullable|string|max:255',
        ];
    }
}
