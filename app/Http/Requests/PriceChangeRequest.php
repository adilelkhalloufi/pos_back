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
            'change_type'            => 'required|in:category,article',
            'start_date'             => 'required|date',
            'modification_type'      => 'required|in:percentage,amount',
            'modification_value'     => 'required_if:change_type,article|numeric|min:0',
            
            // For category type
            'category_values'        => 'required_if:change_type,category|array|min:1',
            'category_values.*.id'   => 'required|integer|exists:categories,id',
            'category_values.*.value'=> 'required|numeric|min:0',
            
            // For article type
            'product_ids'            => 'required_if:change_type,article|array|min:1',
            'product_ids.*'          => 'integer|exists:products,id',
        ];
    }
}
