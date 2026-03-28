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

            'total_command' => 'nullable|numeric',
            'total_payment' => 'nullable|numeric',
            'items' => 'nullable|array',
            'passport_data' => 'nullable|string',

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
