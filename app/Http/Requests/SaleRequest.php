<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaleRequest extends FormRequest
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
            'advance' => 'nullable|numeric',
            'discount' => 'nullable|numeric',
            'rest_a_pay' => 'nullable|numeric',
            'total_command' => 'nullable|numeric',
            'total_payment' => 'nullable|numeric',
            'customer' => 'nullable',
            'items' => 'nullable|array',
            'glass_types' => 'nullable|array',
            'is_invoice' => 'nullable|boolean',
            'is_customer' => 'nullable|boolean',
            'prescription' => 'nullable',
            'assurance' => 'nullable',

        ];
    }

    // public function messages()
    // {
    //     return [
    //         'customer.required' => 'Le champ client est obligatoire',
    //         'is_invoice.required' => 'Le champ facture est obligatoire',
    //         'is_customer.required' => 'Le champ client est obligatoire',

    //     ];
    // }
}
