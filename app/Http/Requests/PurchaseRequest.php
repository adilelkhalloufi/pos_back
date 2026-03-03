<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'details' => 'required|array',
            'reference' => 'nullable|string',
            'paid_method_id' => 'nullable|integer',
            'status' => 'nullable|string',
            'supplier_id' => 'required|integer',
            'public_note' => 'nullable|string',
            'private_note' => 'nullable|string',
 

        ];
    }
}
