<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorefrontProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'short_description' => 'nullable|string|max:255',
            'cover_image' => 'nullable|string|max:255',
        ];
    }
}
