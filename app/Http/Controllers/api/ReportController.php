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

    /** GET /reports/sales-by-item-family?date_start=&date_end= */
    public function salesByItemFamily(Request $request)
    {
        $data = $this->reportService->salesByItemFamily(
            $this->storeId(),
            $request->input('date_start'),
            $request->input('date_end')
        );
        return response()->json($data, Response::HTTP_OK);
    }

    /** GET /reports/sales-by-annexe?date_start=&date_end= */
    public function salesByAnnexe(Request $request)
    {
        $data = $this->reportService->salesByAnnexe(
            $this->storeId(),
            $request->input('date_start'),
            $request->input('date_end')
        );
        return response()->json($data, Response::HTTP_OK);
    }

    /** GET /reports/sales-by-item-family-cashier?date_start=&date_end= */
    public function salesByItemFamilyCashier(Request $request)
    {
        $data = $this->reportService->salesByItemFamilyCashier(
            $this->storeId(),
            $request->input('date_start'),
            $request->input('date_end')
        );
        return response()->json($data, Response::HTTP_OK);
    }

    /** GET /reports/end-of-day?date=YYYY-MM-DD */
    public function endOfDay(Request $request)
    {
        $date = $request->input('date', now()->toDateString());
        $data = $this->reportService->endOfDay($this->storeId(), $date);
        return response()->json($data, Response::HTTP_OK);
    }

    /** GET /reports/sales-journal-margin?date_start=&date_end= */
    public function salesJournalMargin(Request $request)
    {
        $data = $this->reportService->salesJournalMargin(
            $this->storeId(),
            $request->input('date_start'),
            $request->input('date_end')
        );
        return response()->json($data, Response::HTTP_OK);
    }

    /** GET /reports/commands-print-list?date_start=&date_end= */
    public function commandsPrintList(Request $request)
    {
        $data = $this->reportService->commandsPrintList(
            $this->storeId(),
            $request->input('date_start'),
            $request->input('date_end')
        );
        return response()->json($data, Response::HTTP_OK);
    }
}
