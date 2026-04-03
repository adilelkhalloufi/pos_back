<?php

namespace App\Services\Report;

use App\Http\Resources\OrderResource;
use App\Models\OrderItems;
use App\Models\OrderSale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function GetReportData($storeId, $dateStart, $dateEnd, $vendor = null, $category = null, $priceField = 'price')
    {
        $priceField = in_array($priceField, ['price', 'invoice_price'], true) ? $priceField : 'price';

        // Get vendor name if vendor ID is provided
        $vendorName = null;
        if ($vendor !== null) {
            $vendorName = User::find($vendor)?->name;
        }

        // Build base query with joins
        $baseQuery = function ($query) use ($storeId, $dateStart, $dateEnd, $vendor, $category) {
            $query->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'order_items.category_id', '=', 'categories.id')
                ->join('order_sales', 'order_items.order_id', '=', 'order_sales.id')
                ->where('order_sales.store_id', $storeId)
                ->whereDate('order_sales.created_at', '>=', $dateStart)
                ->whereDate('order_sales.created_at', '<=', $dateEnd);

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
            'vendor' => $vendorName,
         ];
    }

    public function GetReportDataByCategory($storeId, $dateStart, $dateEnd, $categoryId, $priceField = 'price')
    {
        $priceField = in_array($priceField, ['price', 'invoice_price'], true) ? $priceField : 'price';

        // Get category name and products data
        $reportData = OrderItems::select(
            'products.name as product_name',
            'categories.name as category_name',
            DB::raw('SUM(order_items.qte) as total_quantity'),
            DB::raw('SUM(order_items.qte * order_items.' . $priceField . ') as total_price')
        )
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'order_items.category_id', '=', 'categories.id')
            ->join('order_sales', 'order_items.order_id', '=', 'order_sales.id')
            ->where('order_sales.store_id', $storeId)
            ->whereDate('order_sales.created_at', '>=', $dateStart)
            ->whereDate('order_sales.created_at', '<=', $dateEnd)
            ->where('categories.id', $categoryId)
            ->groupBy('products.name', 'categories.name')
            ->get();

        // Format the response
        $items = $reportData->map(function ($item) {
            return [
                'name' => $item->product_name,
                'nbr_articles' => (int) $item->total_quantity,
                'ca' => (float) $item->total_price,
            ];
        })->toArray();

        $categoryName = $reportData->first()?->category_name ?? '';
        $totalArticles = $reportData->sum('total_quantity');
        $totalCa = $reportData->sum('total_price');

        return [
            'period' => [
                'start' => $dateStart,
                'end' => $dateEnd,
            ],
            'category_name' => $categoryName,
            'items' => $items,
            'total_articles' => (int) $totalArticles,
            'total_ca' => (float) $totalCa,
        ];
    }

    public function GetOrdersList($storeId, $dateStart, $dateEnd, $vendorId = null)
    {

        $query = OrderSale::with(['orderItems', 'user'])
            ->where('store_id', $storeId)
            ->whereDate('created_at', '>=', $dateStart)
            ->whereDate('created_at', '<=', $dateEnd);

        // Filter by vendor if provided
        if ($vendorId !== null) {
            $query->where('user_id', $vendorId);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return OrderResource::collection($orders);
    }
}
