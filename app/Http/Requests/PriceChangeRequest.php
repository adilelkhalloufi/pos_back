<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PriceChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'products'               => 'required|array|min:1',
            'products.*'             => 'integer|exists:products,id',
             'price_sell_1'           => 'nullable|numeric|min:0',
             'effective_date'         => 'nullable|date',
            'reason'                 => 'nullable|string|max:255',
        ];
    }
}
