<?php

use App\Models\OrderSale;
use Illuminate\Http\Request;

if (function_exists('make')) {

    function make(string $class)
    {
        return app()->make($class);
    }
}

if (!function_exists('padNumber')) {
    function padNumber($number, $length = 4) {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('applyFiltersforDate')) {
    function applyFiltersforDate($query, Request $request)
    {
        if ($request->date_start && $request->date_end) {
            $query->whereBetween('created_at', [
                $request->date_start . ' 00:00:00',
                $request->date_end . ' 23:59:59'
            ]);
        }
        $query->when($request->invoice, function ($query) use ($request) {
            $query->where(OrderSale::COL_IS_INVOICE, $request->invoice);
        });
        return $query;
    }
}

if (!function_exists('applyFilterforSearch')) {
    function applyFiltersforSearch($query, Request $request, $columns)
    {
        if ($request->search) {
            $query->where(function ($query) use ($request, $columns) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', '%' . $request->search . '%');
                }
            });
        }
        return $query;
    } 
}

