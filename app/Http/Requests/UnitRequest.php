<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:100',
            'symbol'      => 'nullable|string|max:20',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ];
    }
}
