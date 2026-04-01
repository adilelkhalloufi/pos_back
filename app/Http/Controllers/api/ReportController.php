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

}
