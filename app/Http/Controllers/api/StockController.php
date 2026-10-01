<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StockMovementRequest;
use App\Http\Resources\StockMovementResource;
use App\Models\StockMovement;
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

    /**
     * Get stock movements for a specific product.
     * Endpoint: /products/{id}/stock-movements?from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function productStockMovements(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:200'],
        ]);

        $query = StockMovement::query()
            ->with(['product', 'sourceStore', 'targetStore', 'store', 'user'])
            ->where(StockMovement::COL_PRODUCT_ID, $id)
            ->where(StockMovement::COL_STORE_ID, currentStoreId())
            ->orderByDesc('created_at');

        if (!empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if (!empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        $perPage = (int) ($validated['per_page'] ?? 200);
        $movements = $query->paginate($perPage);

        return response()->json([
            'product_id' => $id,
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
            'data' => StockMovementResource::collection($movements->getCollection()),
            'pagination' => [
                'current_page' => $movements->currentPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
                'last_page' => $movements->lastPage(),
            ],
        ], Response::HTTP_OK);
    }

   

  
 
 

   

  
}
