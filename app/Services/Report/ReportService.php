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

    public function GetDailyCategoryReport($storeId, $dateStart, $dateEnd)
    {
        // Get all categories for this store (or all categories if store_id doesn't filter properly)
        $allCategories = \App\Models\Category::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get()
            ->keyBy('id');

        // Get sales data grouped by date and category
        $salesData = OrderItems::select(
            DB::raw('DATE(order_sales.created_at) as sale_date'),
            'order_items.category_id as category_id',
            'categories.name as category_name',
            DB::raw('SUM(order_items.qte) as total_quantity'),
            DB::raw('SUM(order_items.qte * order_items.invoice_price) as total_amount')
        )
            ->join('order_sales', 'order_items.order_id', '=', 'order_sales.id')
            ->leftJoin('categories', 'order_items.category_id', '=', 'categories.id')
            ->where('order_sales.store_id', $storeId)
            ->where('order_sales.created_at', '>=', $dateStart . ' 00:00:00')
            ->where('order_sales.created_at', '<=', $dateEnd . ' 23:59:59')
            ->whereNull('order_sales.deleted_at')
            ->groupBy('sale_date', 'order_items.category_id', 'categories.name')
            ->orderBy('sale_date', 'asc')
            ->orderBy('categories.name', 'asc')
            ->get();

        // Group sales data by date
        $salesByDate = [];
        foreach ($salesData as $row) {
            $date = $row->sale_date;
            $categoryId = $row->category_id;

            if (!isset($salesByDate[$date])) {
                $salesByDate[$date] = [];
            }

            $salesByDate[$date][$categoryId] = [
                'category_id' => $categoryId,
                'category_name' => $row->category_name ?? 'Uncategorized',
                'total_items' => (int) $row->total_quantity,
                'total_amount' => (float) $row->total_amount,
            ];
        }

        // Generate date range
        $startDate = \Carbon\Carbon::parse($dateStart);
        $endDate = \Carbon\Carbon::parse($dateEnd);
        $dateRange = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateRange[] = $date->format('Y-m-d');
        }

        // Build final report with all categories for each day
        $report = [];
        foreach ($dateRange as $date) {
            $categoriesForDay = [];

            // Add all categories with their sales or 0 values
            foreach ($allCategories as $categoryId => $category) {
                if (isset($salesByDate[$date][$categoryId])) {
                    $categoriesForDay[] = $salesByDate[$date][$categoryId];
                } else {
                    $categoriesForDay[] = [
                        'category_id' => $categoryId,
                        'category_name' => $category->name,
                        'total_items' => 0,
                        'total_amount' => 0.0,
                    ];
                }
            }

            // Add uncategorized items if they exist for this date
            if (isset($salesByDate[$date][null])) {
                $categoriesForDay[] = $salesByDate[$date][null];
            }

            $report[] = [
                'date' => $date,
                'categories' => $categoriesForDay
            ];
        }

        return [
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'daily_sales' => $report,
        ];
    }
}
