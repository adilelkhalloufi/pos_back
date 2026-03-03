<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StockMovementRequest;
use App\Services\Stock\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class StockController extends BaseController
{
 
    public function __construct(
        private StockService $stockService
        )
    {
         
    }

    /**
     * Get stock movements list
     */
    public function index(StockMovementRequest $request): JsonResponse
    {
        $filters = $request->getFilters();
        $pagination = $request->getPagination();
        $sorting = $request->getSorting();

        // Merge all parameters
        $params = array_merge($filters, $pagination, $sorting);

        $movements = $this->stockService->getStockMovements($params);

        return response()->json($movements, Response::HTTP_OK);
    }

   

  
 
 

   

  
}
