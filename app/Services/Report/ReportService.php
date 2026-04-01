<?php

namespace App\Services\Report;

use App\Models\OrderItems;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function GetReportData($storeId, $dateStart, $dateEnd, $vendor = null, $category = null, $priceField = 'price')
    {
        $priceField = in_array($priceField, ['price', 'invoice_price'], true) ? $priceField : 'price';

        // Build base query with joins
        $baseQuery = function ($query) use ($storeId, $dateStart, $dateEnd, $vendor, $category) {
            $query->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'order_items.category_id', '=', 'categories.id')
                ->join('order_sales', 'order_items.order_id', '=', 'order_sales.id')
                ->where('order_sales.store_id', $storeId)
                ->whereBetween('order_sales.created_at', [$dateStart, $dateEnd]);

            // Filter by vendor if provided
            if ($vendor !== null) {
                $query->where('order_sales.user_id', $vendor);
            }

            // Filter by category if provided
            if ($category !== null) {
                $query->where('categories.id', $category);
            }
        };

        // Query grouped by product
        $byProduct = OrderItems::select(
            'products.id',
            'products.name as product_name',
            'categories.id as category_id',
            'categories.name as category_name',
            DB::raw('SUM(order_items.qte) as total_quantity'),
            DB::raw('SUM(order_items.qte * order_items.' . $priceField . ') as total_price')
        )
            ->tap($baseQuery)
            ->groupBy('products.id', 'products.name', 'categories.id', 'categories.name')
            ->get();

        // Query grouped by category
        $byCategory = OrderItems::select(
            'categories.id',
            'categories.name as category_name',
            DB::raw('SUM(order_items.qte) as total_quantity'),
            DB::raw('SUM(order_items.qte * order_items.' . $priceField . ') as total_price')
        )
            ->tap($baseQuery)
            ->groupBy('categories.id', 'categories.name')
            ->get();

        return [
            'by_product' => $byProduct,
            'by_category' => $byCategory,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'vendor' => $vendor,
        ];
    }
}
