<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name'                    => 'nullable|string|max:255',
            'currency'                        => 'nullable|string|max:10',
            'document_header'                 => 'nullable|string',
            'document_footer'                 => 'nullable|string',
            'order_prefix'                    => 'nullable|string|max:10',
            'invoice_prefix'                  => 'nullable|string|max:10',
            'purchase_prefix'                 => 'nullable|string|max:10',
            // Printing
            'max_print_copies'                => 'nullable|integer|min:1|max:99',
            // Secondary display
            'secondary_display_enabled'       => 'nullable|boolean',
            'secondary_display_connection'    => 'nullable|in:com,network',
            'secondary_display_com_port'      => 'nullable|string|max:20',
            'secondary_display_x'             => 'nullable|integer|min:0',
            'secondary_display_y'             => 'nullable|integer|min:0',
            'secondary_display_width'         => 'nullable|integer|min:100',
            'secondary_display_height'        => 'nullable|integer|min:100',
            // Passport reader
            'passport_reader_enabled'         => 'nullable|boolean',
            'passport_reader_com_port'        => 'nullable|string|max:20',
            'passport_reader_baud_rate'       => 'nullable|integer',
            'passport_reader_provider'        => 'nullable|string|max:100',
        ];
    }
}
