<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Services\Report\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportController extends BaseController
{
    public function __construct(private readonly ReportService $reportService)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'vendor' => 'nullable|exists:users,id',
            'category' => 'nullable|exists:categories,id',
            'price_field' => 'nullable|in:price,invoice_price',
        ]);

        $storeId = $this->storeId();
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $vendor = $request->input('vendor');
        $category = $request->input('category');
        $priceField = $request->input('price_field', 'price');

        $reportData = $this->reportService->GetReportData($storeId, $dateStart, $dateEnd, $vendor, $category, $priceField);

        return response()->json($reportData);
    }

    public function salesByAnnexe(Request $request)
    {
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'category_id' => 'nullable|exists:categories,id',
            'price_field' => 'nullable|in:price,invoice_price',
        ]);

        $storeId = $this->storeId();
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $categoryId = $request->input('category_id');
        $priceField = $request->input('price_field', 'price');

        $reportData = $this->reportService->GetReportDataByCategory($storeId, $dateStart, $dateEnd, $categoryId, $priceField);

        return response()->json($reportData);
    }

    public function OrderList(Request $request)
    {
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
            'vendor_id' => 'nullable|exists:users,id',
        ]);

        $storeId = $this->storeId();
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');
        $vendorId = $request->input('vendor_id');

        $orders = $this->reportService->GetOrdersList($storeId, $dateStart, $dateEnd, $vendorId);

        return response()->json($orders);
    }

    public function dailyCategoryReport(Request $request)
    {
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date|after_or_equal:date_start',
        ]);

        $storeId = $this->storeId();
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        // Extended Debug: Check various scenarios
        $allSalesCount = \App\Models\OrderSale::count();
        $storeAllSales = \App\Models\OrderSale::where('store_id', $storeId)->count();
        $salesWithoutDeleted = \App\Models\OrderSale::where('store_id', $storeId)->whereNull('deleted_at')->count();

        $salesInDateRange = \App\Models\OrderSale::where('store_id', $storeId)
            ->whereNull('deleted_at')
            ->where('created_at', '>=', $dateStart . ' 00:00:00')
            ->where('created_at', '<=', $dateEnd . ' 23:59:59')
            ->count();

        // Get a sample sale to see the date format
        $sampleSale = \App\Models\OrderSale::where('store_id', $storeId)
            ->whereNull('deleted_at')
            ->select('id', 'order_number', 'created_at', 'store_id')
            ->first();

        $reportData = $this->reportService->GetDailyCategoryReport($storeId, $dateStart, $dateEnd);

        // Add extensive debug info
        $reportData['debug'] = [
            'store_id' => $storeId,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
            'all_sales_in_db' => $allSalesCount,
            'sales_for_this_store' => $storeAllSales,
            'sales_not_deleted' => $salesWithoutDeleted,
            'sales_in_date_range' => $salesInDateRange,
            'sample_sale' => $sampleSale,
        ];

        return response()->json($reportData);
    }
}
