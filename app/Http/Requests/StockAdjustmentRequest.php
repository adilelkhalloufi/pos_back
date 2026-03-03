<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
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
     */    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'new_quantity' => ['required', 'numeric', 'min:0'],
            'reason' => ['required', 'string', 'max:500'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'store_id' => ['required', 'exists:stores,id'],
        ];
    }

    /**
     * Get custom error messages.
     */    public function messages(): array
    {
        return [
            'product_id.required' => 'Product is required',
            'product_id.exists' => 'Selected product does not exist',
            'new_quantity.required' => 'New quantity is required',
            'new_quantity.numeric' => 'New quantity must be a number',
            'new_quantity.min' => 'New quantity cannot be negative',
            'reason.required' => 'Reason for adjustment is required',
            'reason.string' => 'Reason must be text',
            'reason.max' => 'Reason cannot exceed 500 characters',
            'unit_cost.numeric' => 'Unit cost must be a number',
            'unit_cost.min' => 'Unit cost cannot be negative',
            'store_id.required' => 'Store is required',
            'store_id.exists' => 'Selected store does not exist',
        ];
    }
}
