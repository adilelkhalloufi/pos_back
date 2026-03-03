<?php

namespace App\Http\Requests;

use App\Enums\EnumStockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMovementRequest extends FormRequest
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
            // Filter parameters (all optional)
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'source_store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'target_store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'type' => ['nullable', 'string', 'in:sale,purchase,transfer,adjustment,inventory'],
            'direction' => ['nullable', 'string', 'in:in,out'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'referenceable_type' => ['nullable', 'string', 'max:255'],
            'referenceable_id' => ['nullable', 'integer'],

            // Date filters
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'created_at_from' => ['nullable', 'date'],
            'created_at_to' => ['nullable', 'date', 'after_or_equal:created_at_from'],

            // Quantity filters
            'quantity_min' => ['nullable', 'numeric', 'min:0'],
            'quantity_max' => ['nullable', 'numeric', 'min:0'],

            // Cost filters
            'unit_cost_min' => ['nullable', 'numeric', 'min:0'],
            'unit_cost_max' => ['nullable', 'numeric', 'min:0'],
            'total_cost_min' => ['nullable', 'numeric', 'min:0'],
            'total_cost_max' => ['nullable', 'numeric', 'min:0'],

            // Pagination and sorting
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'sort_by' => ['nullable', 'string', 'in:id,created_at,updated_at,quantity,type,direction'],
            'sort_direction' => ['nullable', 'string', 'in:asc,desc'],

            // Search
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages.
     */    public function messages(): array
    {
        return [
            'product_id.exists' => 'Selected product does not exist',
            'source_store_id.exists' => 'Selected source store does not exist',
            'target_store_id.exists' => 'Selected target store does not exist',
            'store_id.exists' => 'Selected store does not exist',
            'type.in' => 'Invalid movement type. Must be one of: sale, purchase, transfer, adjustment, inventory',
            'direction.in' => 'Direction must be either "in" or "out"',
            'user_id.exists' => 'Selected user does not exist',
            'date_to.after_or_equal' => 'End date must be after or equal to start date',
            'created_at_to.after_or_equal' => 'Created end date must be after or equal to created start date',
            'quantity_min.numeric' => 'Minimum quantity must be a number',
            'quantity_min.min' => 'Minimum quantity cannot be negative',
            'quantity_max.numeric' => 'Maximum quantity must be a number',
            'quantity_max.min' => 'Maximum quantity cannot be negative',
            'unit_cost_min.numeric' => 'Minimum unit cost must be a number',
            'unit_cost_min.min' => 'Minimum unit cost cannot be negative',
            'unit_cost_max.numeric' => 'Maximum unit cost must be a number',
            'unit_cost_max.min' => 'Maximum unit cost cannot be negative',
            'total_cost_min.numeric' => 'Minimum total cost must be a number',
            'total_cost_min.min' => 'Minimum total cost cannot be negative',
            'total_cost_max.numeric' => 'Maximum total cost must be a number',
            'total_cost_max.min' => 'Maximum total cost cannot be negative',
            'per_page.integer' => 'Items per page must be a number',
            'per_page.min' => 'Items per page must be at least 1',
            'per_page.max' => 'Items per page cannot exceed 100',
            'page.integer' => 'Page must be a number',
            'page.min' => 'Page must be at least 1',
            'sort_by.in' => 'Invalid sort field',
            'sort_direction.in' => 'Sort direction must be "asc" or "desc"',
            'search.max' => 'Search query cannot exceed 255 characters',
        ];
    }

    /**
     * Get the filters from the request.
     */
    public function getFilters(): array
    {
        return [
            'product_id' => $this->input('product_id'),
            'source_store_id' => $this->input('source_store_id'),
            'target_store_id' => $this->input('target_store_id'),
            'store_id' => $this->input('store_id'),
            'type' => $this->input('type'),
            'direction' => $this->input('direction'),
            'user_id' => $this->input('user_id'),
            'referenceable_type' => $this->input('referenceable_type'),
            'referenceable_id' => $this->input('referenceable_id'),
            'date_from' => $this->input('date_from'),
            'date_to' => $this->input('date_to'),
            'created_at_from' => $this->input('created_at_from'),
            'created_at_to' => $this->input('created_at_to'),
            'quantity_min' => $this->input('quantity_min'),
            'quantity_max' => $this->input('quantity_max'),
            'unit_cost_min' => $this->input('unit_cost_min'),
            'unit_cost_max' => $this->input('unit_cost_max'),
            'total_cost_min' => $this->input('total_cost_min'),
            'total_cost_max' => $this->input('total_cost_max'),
            'search' => $this->input('search'),
        ];
    }

    /**
     * Get pagination parameters.
     */
    public function getPagination(): array
    {
        return [
            'per_page' => $this->input('per_page', 15),
            'page' => $this->input('page', 1),
        ];
    }

    /**
     * Get sorting parameters.
     */
    public function getSorting(): array
    {
        return [
            'sort_by' => $this->input('sort_by', 'created_at'),
            'sort_direction' => $this->input('sort_direction', 'desc'),
        ];
    }
}
