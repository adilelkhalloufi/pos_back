<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseImportRequest extends FormRequest
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
            'supplier_id' => 'required|integer|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'reference' => 'nullable|string',
            'note' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.codebar' => 'nullable|string',
            'products.*.code_supplier' => 'nullable|string',
            'products.*.product_name' => 'required|string',
            'products.*.purchase_price' => 'required|numeric|min:0',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.category' => 'nullable|string',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Supplier is required',
            'supplier_id.exists' => 'Supplier does not exist',
            'purchase_date.required' => 'Purchase date is required',
            'products.required' => 'At least one product is required',
            'products.*.product_name.required' => 'Product name is required',
            'products.*.purchase_price.required' => 'Purchase price is required',
            'products.*.purchase_price.min' => 'Purchase price must be positive',
            'products.*.quantity.required' => 'Quantity is required',
            'products.*.quantity.min' => 'Quantity must be greater than 0',
        ];
    }
}
