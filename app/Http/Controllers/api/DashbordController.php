<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Models\OrderSale;
use App\Models\OrderItems;
use App\Models\TypeGlasses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Throwable;

class DashbordController extends BaseController
{
    public function Dashbord(Request $request)
    {
        try {
            $storeId = $this->storeId();
            $year = $request->input('year', Carbon::now()->year);

            $currentDate = Carbon::now();
            $currentMonth = $currentDate->month;

            $dailySales = $this->getDailySalesTotal($currentDate, $storeId, $year);
            $monthlySales = $this->getMonthlySalesTotal($currentMonth, $year, $storeId);
            $dailyOrderCount = $this->getDailyOrderCount($currentDate, $storeId, $year);
            $monthlyOrderCount = $this->getMonthlyOrderCount($currentMonth, $year, $storeId);
            $monthlyRevenue = $this->getMonthlyRevenueData($year, $storeId);
            $recentOrders = $this->getRecentOrdersWithCustomers($storeId, $year);
            $ordersByGender = $this->getOrdersByGender($storeId, $year);
            $caByVendor = $this->getCAByVendor($storeId, $year);
            $topSellingProducts = $this->getTopSellingProducts($storeId, $year);
            $topSellingTypeGlasses = $this->getTopSellingTypeGlasses($storeId, $year);

            return response()->json([
                "monthly_order_count" => $monthlyOrderCount,
                "daily_order_count" => $dailyOrderCount,
                "daily_sales" => $dailySales,
                "monthly_sales" => $monthlySales,
                "monthly_revenue" => $monthlyRevenue,
                "recent_orders" => $recentOrders,
                "orders_by_gender" => $ordersByGender,
                "ca_by_vendor" => $caByVendor,
                "top_selling_products" => $topSellingProducts,
                "top_selling_type_glasses" => $topSellingTypeGlasses,
            ], 200);
        } catch (Throwable $exception) {
            return response()->json(
                ["error" => "Failed to retrieve dashboard data: " . $exception->getMessage()],
                500,
            );
        }
    }

    /**
     * Get total sales for the current day
     */
    private function getDailySalesTotal(Carbon $date, int $storeId, int $year): float
    {
        return OrderSale::whereDate(OrderSale::COL_CREATED_AT, $date)
            ->whereYear(OrderSale::COL_CREATED_AT, $year)
            ->where(OrderSale::COL_STORE_ID, $storeId)
            ->sum(OrderSale::COL_TOTAL_COMMAND);
    }

    /**
     * Get total sales for the current month
     */
    private function getMonthlySalesTotal(int $month, int $year, int $storeId): float
    {
        return OrderSale::whereMonth(OrderSale::COL_CREATED_AT, $month)
            ->whereYear(OrderSale::COL_CREATED_AT, $year)
            ->where(OrderSale::COL_STORE_ID, $storeId)
            ->sum(OrderSale::COL_TOTAL_COMMAND);
    }

    /**
     * Get number of orders for the current day
     */
    private function getDailyOrderCount(Carbon $date, int $storeId, int $year): int
    {
        return OrderSale::whereDate(OrderSale::COL_CREATED_AT, $date)
            ->whereYear(OrderSale::COL_CREATED_AT, $year)
            ->where(OrderSale::COL_STORE_ID, $storeId)
            ->count();
    }

    /**
     * Get number of orders for the current month
     */
    private function getMonthlyOrderCount(int $month, int $year, int $storeId): int
    {
        return OrderSale::whereMonth(OrderSale::COL_CREATED_AT, $month)
            ->whereYear(OrderSale::COL_CREATED_AT, $year)
            ->where(OrderSale::COL_STORE_ID, $storeId)
            ->count();
    }

    /**
     * Get monthly revenue data for all months in the current year
     */
    private function getMonthlyRevenueData(int $year, int $storeId): array
    {
        $revenues = OrderSale::selectRaw('MONTH(created_at) as month, SUM(total_command) as revenue')
            ->whereYear(OrderSale::COL_CREATED_AT, $year)
            ->where(OrderSale::COL_STORE_ID, $storeId)
            ->groupByRaw('MONTH(created_at)')
            ->pluck('revenue', 'month')
            ->toArray();

        $monthlyRevenue = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthlyRevenue[] = $revenues[$month] ?? 0;
        }

        return $monthlyRevenue;
    }

    /**
     * Get the last 5 order sales with their customers
     */
    private function getRecentOrdersWithCustomers(int $storeId, int $year): array
    {
        return OrderSale::where(OrderSale::COL_STORE_ID, $storeId)
            ->with('customer') // Assuming there's a customer relationship defined
            ->whereYear(OrderSale::COL_CREATED_AT, $year)

            ->orderBy(OrderSale::COL_CREATED_AT, 'desc')
            ->limit(5)
            ->get()
            ->toArray();
    }

    /**
     * Get orders grouped by client gender
     */
    private function getOrdersByGender(int $storeId, int $year)
    {
        $genderCounts = OrderSale::join('customers', 'order_sales.customer_id', '=', 'customers.id')
            ->where('order_sales.store_id', $storeId)
            ->whereYear('order_sales.' . OrderSale::COL_CREATED_AT, $year)
            ->selectRaw('customers.gender, COUNT(*) as count')
            ->groupBy('customers.gender')
            ->pluck('count', 'gender')
            ->toArray();

        // Map to your format (assuming gender: 0=female, 1=male)
        $result = [
            ['browser' => 'Male', 'visitors' => $genderCounts[1] ?? 0, 'fill' => 'var(--color-male)'],
            ['browser' => 'Female', 'visitors' => $genderCounts[0] ?? 0, 'fill' => 'var(--color-female)'],
            ['browser' => 'Unknown', 'visitors' => ($genderCounts[''] ?? 0) + ($genderCounts[null] ?? 0), 'fill' => 'var(--color-unknown)'],
        ];

        return $result;
    }

    /**
     * Get CA (Chiffre d'Affaires / Revenue) by vendor/seller
     */
    private function getCAByVendor(int $storeId, int $year): array
    {
        return OrderSale::join('users', 'order_sales.user_id', '=', 'users.id')
            ->where('order_sales.store_id', $storeId)
            ->whereYear('order_sales.' . OrderSale::COL_CREATED_AT, $year)
            ->selectRaw('users.id, users.name, SUM(order_sales.total_command) as total_ca, COUNT(order_sales.id) as order_count')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_ca')
            ->get()
            ->map(function ($item) {
                return [
                    'vendor_id' => $item->id,
                    'vendor_name' => $item->name,
                    'total_ca' => (float) $item->total_ca,
                    'order_count' => (int) $item->order_count,
                ];
            })
            ->toArray();
    }

    /**
     * Get top 10 selling products (excluding type glasses)
     */
    private function getTopSellingProducts(int $storeId, int $year): array
    {
        return OrderItems::whereHas('order', function ($query) use ($storeId, $year) {
            $query->where(OrderSale::COL_STORE_ID, $storeId)
                  ->whereYear(OrderSale::COL_CREATED_AT, $year);
        })
        ->where('product_type', '!=', TypeGlasses::class)
        ->selectRaw('product_id, product_type, name, SUM(qte) as total_sold')
        ->groupBy('product_id', 'product_type', 'name')
        ->orderByDesc('total_sold')
        ->limit(10)
        ->get()
        ->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_type' => $item->product_type,
                'name' => $item->name,
                'total_sold' => (int) $item->total_sold,
            ];
        })
        ->toArray();
    }

    /**
     * Get top 10 selling type glasses
     */
    private function getTopSellingTypeGlasses(int $storeId, int $year): array
    {
        return OrderItems::whereHas('order', function ($query) use ($storeId, $year) {
            $query->where(OrderSale::COL_STORE_ID, $storeId)
                  ->whereYear(OrderSale::COL_CREATED_AT, $year);
        })
        ->where('product_type', TypeGlasses::class)
        ->selectRaw('product_id, product_type, name, SUM(qte) as total_sold')
        ->groupBy('product_id', 'product_type', 'name')
        ->orderByDesc('total_sold')
        ->limit(10)
        ->get()
        ->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'product_type' => $item->product_type,
                'name' => $item->name,
                'total_sold' => (int) $item->total_sold,
            ];
        })
        ->toArray();
    }
}
