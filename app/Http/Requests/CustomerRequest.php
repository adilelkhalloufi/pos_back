<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
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

            Customer::COL_NAME => 'nullable',
            Customer::COL_PHONE => 'nullable',
            Customer::COL_GENDER => 'nullable',
            Customer::COL_EMAIL => 'nullable',
            Customer::COL_CIN => 'nullable',
            Customer::COL_BIRTHDAY => 'nullable'

        ];
    }
}
