<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrintProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => 'required|string|max:100',
            'printer_name'     => 'nullable|string|max:255',
            'connection_type'  => 'nullable|in:usb,network,com',
            'com_port'         => 'nullable|string|max:20',
            'max_copies'       => 'nullable|integer|min:1|max:99',
            'is_default'       => 'nullable|boolean',
            'is_active'        => 'nullable|boolean',
        ];
    }
}
