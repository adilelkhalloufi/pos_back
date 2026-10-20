<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorefrontOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:60',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:1000',
            'note' => 'nullable|string|max:2000',
            'currency' => 'nullable|string|max:8',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.variant_id' => 'nullable|integer|exists:product_variants,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
        ];
    }
}
