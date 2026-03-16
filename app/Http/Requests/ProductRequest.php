<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Handle barcodes if sent as JSON string
        if ($this->has('barcodes') && is_string($this->barcodes)) {
            $decoded = json_decode($this->barcodes, true);
            
            if (is_array($decoded)) {
                // Handle array of objects like [{"barcode":"123"},{"barcode":"456"}]
                $barcodes = array_map(function($item) {
                    if (is_array($item) && isset($item['barcode'])) {
                        return $item['barcode'];
                    }
                    return $item;
                }, $decoded);
                
                $this->merge(['barcodes' => array_filter($barcodes)]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:255',
            'price'            => 'nullable|numeric',
            'price_buy'        => 'nullable|numeric|min:0',
            'price_sell_1'     => 'nullable|numeric|min:0',
            'supplier_code'    => 'nullable|string|max:100',
            'reference'        => 'nullable|string|max:255',
            'codebar'          => 'nullable|string|max:255',
            'barcodes'         => 'nullable|array',
            'barcodes.*'       => 'nullable|string|max:255',
            'slug'             => 'nullable|string|max:255',
            'description'      => 'nullable|string',
            'image'            => 'nullable',
            'stock_alert'      => 'nullable|integer',
             'is_active'        => 'nullable',
            'is_stockable'     => 'nullable|boolean',
            'archive'          => 'nullable',
            'quantity'         => 'nullable|integer|min:0',
             'category_id'      => 'nullable|integer|exists:categories,id',
            'unit_id'          => 'nullable|integer|exists:units,id',
            'print_profile_id' => 'nullable|integer|exists:print_profiles,id',
        ];
    }


    public function messages(): array
    {
        return [
            'name.required' => __('product.errors.name'),
            'price.required' => __('product.errors.price'),
            'price.numeric' => __('product.errors.price'),
             'category_id.exists' => __('product.errors.category_not_exist'),
            'quantity.min' => __('product.errors.quantity_min'),
        ];
    }
}
