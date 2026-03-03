<?php

namespace App\Http\Requests;

use App\Models\StoreProducts;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            StoreProducts::COL_STORE_ID => 'required|exists:stores,id',
            StoreProducts::COL_PRODUCT_ID => 'required|exists:products,id',
            StoreProducts::COL_PRICE => 'nullable|numeric|min:0',
            StoreProducts::COL_COST => 'nullable|numeric|min:0',
            StoreProducts::COL_STOCK => 'nullable|numeric|min:0',
        ];
    }
}
