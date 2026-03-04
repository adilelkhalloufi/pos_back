<?php

namespace App\Services\Report;

use App\Models\OrderItems;
use App\Models\OrderSale;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // ─────────────────────────────────────────────────────────────────────────
    // 1. Sales by article & family (category)
    // ─────────────────────────────────────────────────────────────────────────
    public function salesByItemFamily(int $storeId, ?string $dateStart, ?string $dateEnd): array
    {
        $query = OrderItems::select(
            'order_items.product_id',
            'order_items.name as product_name',
            'categories.name as category_name',
            DB::raw('SUM(order_items.qte) as total_qty'),
            DB::raw('SUM(order_items.price * order_items.qte) as total_ht')
        )
        ->join('order_sales', 'order_items.order_id', '=', 'order_sales.id')
        ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->where('order_sales.store_id', $storeId)
        ->whereNull('order_sales.cancelled_at')
        ->groupBy('order_items.product_id', 'order_items.name', 'categories.name');

        $this->applyDateRange($query, $dateStart, $dateEnd, 'order_sales.created_at');

        return $query->orderBy('total_ht', 'desc')->get()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Sales by annexe (category totals)
    // ─────────────────────────────────────────────────────────────────────────
    public function salesByAnnexe(int $storeId, ?string $dateStart, ?string $dateEnd): array
    {
        $query = OrderItems::select(
            'categories.id as category_id',
            'categories.name as category_name',
            DB::raw('COUNT(DISTINCT order_sales.id) as order_count'),
            DB::raw('SUM(order_items.qte) as total_qty'),
            DB::raw('SUM(order_items.price * order_items.qte) as total_ht')
        )
        ->join('order_sales', 'order_items.order_id', '=', 'order_sales.id')
        ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->where('order_sales.store_id', $storeId)
        ->whereNull('order_sales.cancelled_at')
        ->groupBy('categories.id', 'categories.name');

        $this->applyDateRange($query, $dateStart, $dateEnd, 'order_sales.created_at');

        return $query->orderBy('total_ht', 'desc')->get()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Sales by article & family per cashier
    // ─────────────────────────────────────────────────────────────────────────
    public function salesByItemFamilyCashier(int $storeId, ?string $dateStart, ?string $dateEnd): array
    {
        $query = OrderItems::select(
            'users.id as cashier_id',
            'users.name as cashier_name',
            'order_items.product_id',
            'order_items.name as product_name',
            'categories.name as category_name',
            DB::raw('SUM(order_items.qte) as total_qty'),
            DB::raw('SUM(order_items.price * order_items.qte) as total_ht')
        )
        ->join('order_sales', 'order_items.order_id', '=', 'order_sales.id')
        ->join('users', 'order_sales.user_id', '=', 'users.id')
        ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
        ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
        ->where('order_sales.store_id', $storeId)
        ->whereNull('order_sales.cancelled_at')
        ->groupBy('users.id', 'users.name', 'order_items.product_id', 'order_items.name', 'categories.name');

        $this->applyDateRange($query, $dateStart, $dateEnd, 'order_sales.created_at');

        return $query->orderBy('users.name')->orderBy('total_ht', 'desc')->get()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. End-of-day summary
    // ─────────────────────────────────────────────────────────────────────────
    public function endOfDay(int $storeId, string $date): array
    {
        $orders = OrderSale::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->whereNull('cancelled_at')
            ->with(['orderItems', 'payments'])
            ->get();

        $totalSales     = $orders->sum('total_command');
        $totalPaid      = $orders->sum('total_payment');
        $totalDiscount  = $orders->sum('discount');
        $totalRest      = $orders->sum('rest_a_pay');
        $orderCount     = $orders->count();
        $cancelledCount = OrderSale::where('store_id', $storeId)
            ->whereDate('created_at', $date)
            ->whereNotNull('cancelled_at')
            ->count();

        $byPaymentMode = DB::table('payemnts')
            ->join('order_sales', 'payemnts.order_id', '=', 'order_sales.id')
            ->leftJoin('mode_payemnts', 'payemnts.mode_payemnt_id', '=', 'mode_payemnts.id')
            ->where('order_sales.store_id', $storeId)
            ->whereDate('payemnts.created_at', $date)
            ->select('mode_payemnts.name as mode', DB::raw('SUM(payemnts.amount) as total'))
            ->groupBy('mode_payemnts.name')
            ->get()->toArray();

        return compact(
            'date', 'orderCount', 'cancelledCount',
            'totalSales', 'totalPaid', 'totalDiscount', 'totalRest',
            'byPaymentMode'
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Sales journal with margin HT & margin rate %
    // ─────────────────────────────────────────────────────────────────────────
    public function salesJournalMargin(int $storeId, ?string $dateStart, ?string $dateEnd): array
    {
        $query = OrderItems::select(
            'order_sales.id as order_id',
            'order_sales.order_number',
            'order_sales.created_at as sale_date',
            'users.name as cashier',
            'order_items.product_id',
            'order_items.name as product_name',
            'order_items.qte',
            'order_items.price as price_sell',
            DB::raw('COALESCE(products.price_buy, 0) as price_cost'),
            DB::raw('(order_items.price * order_items.qte) as total_ht'),
            DB::raw('(COALESCE(products.price_buy, 0) * order_items.qte) as total_cost'),
            DB::raw('((order_items.price - COALESCE(products.price_buy, 0)) * order_items.qte) as margin_ht'),
            DB::raw('CASE WHEN order_items.price > 0
                THEN ROUND(((order_items.price - COALESCE(products.price_buy, 0)) / order_items.price) * 100, 2)
                ELSE 0 END as margin_rate')
        )
        ->join('order_sales', 'order_items.order_id', '=', 'order_sales.id')
        ->join('users', 'order_sales.user_id', '=', 'users.id')
        ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
        ->where('order_sales.store_id', $storeId)
        ->whereNull('order_sales.cancelled_at');

        $this->applyDateRange($query, $dateStart, $dateEnd, 'order_sales.created_at');

        return $query->orderBy('order_sales.created_at')->get()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Print list of orders with PV1 & PV2
    // ─────────────────────────────────────────────────────────────────────────
    public function commandsPrintList(int $storeId, ?string $dateStart, ?string $dateEnd): array
    {
        $query = OrderItems::select(
            'order_sales.id as order_id',
            'order_sales.order_number',
            'order_sales.created_at as sale_date',
            'order_sales.total_command',
            'order_sales.discount',
            DB::raw('COALESCE(customers.first_name, \'\') as customer_first_name'),
            DB::raw('COALESCE(customers.last_name, \'\') as customer_last_name'),
            'users.name as cashier',
            'order_items.product_id',
            'order_items.name as product_name',
            'order_items.qte',
            'order_items.price as price_sell_1_used',
            DB::raw('COALESCE(products.price_sell_1, order_items.price, 0) as price_sell_1'),
            DB::raw('COALESCE(products.price_sell_2, 0) as price_sell_2')
        )
        ->join('order_sales', 'order_items.order_id', '=', 'order_sales.id')
        ->join('users', 'order_sales.user_id', '=', 'users.id')
        ->leftJoin('customers', 'order_sales.customer_id', '=', 'customers.id')
        ->leftJoin('products', 'order_items.product_id', '=', 'products.id')
        ->where('order_sales.store_id', $storeId);

        $this->applyDateRange($query, $dateStart, $dateEnd, 'order_sales.created_at');

        return $query->orderBy('order_sales.created_at')->get()->toArray();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Shared helper
    // ─────────────────────────────────────────────────────────────────────────
    private function applyDateRange($query, ?string $start, ?string $end, string $col): void
    {
        if ($start) {
            $query->whereDate($col, '>=', $start);
        }
        if ($end) {
            $query->whereDate($col, '<=', $end);
        }
    }
}
