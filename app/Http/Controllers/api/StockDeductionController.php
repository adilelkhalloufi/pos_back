<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\Stock\StockDeductionService;
use App\Models\TheoreticalConsumption;
use Exception;

class StockDeductionController
{
    protected $stockDeductionService;

    public function __construct(StockDeductionService $stockDeductionService)
    {
        $this->stockDeductionService = $stockDeductionService;
    }

    /**
     * Manually deduct stock for a menu item (for testing)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deduct(Request $request)
    {
        try {
            $validated = $request->validate([
                'menu_item_id' => 'required|exists:menu_items,id',
                'quantity' => 'required|numeric|min:0.01',
                'store_id' => 'required|exists:stores,id',
                'user_id' => 'nullable|exists:users,id',
            ]);

            $userId = $validated['user_id'] ?? auth()->id() ?? 1;

            $result = $this->stockDeductionService->deductMenuItemStock(
                $validated['menu_item_id'],
                $validated['quantity'],
                $validated['store_id'],
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock deducted successfully',
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check if a menu item can be sold (sufficient stock)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        try {
            $validated = $request->validate([
                'menu_item_id' => 'required|exists:menu_items,id',
                'quantity' => 'required|numeric|min:0.01',
                'store_id' => 'required|exists:stores,id',
            ]);

            $result = $this->stockDeductionService->checkMenuItemAvailability(
                $validated['menu_item_id'],
                $validated['quantity'],
                $validated['store_id']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Simulate stock deduction without actually deducting
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function simulate(Request $request)
    {
        try {
            $validated = $request->validate([
                'menu_item_id' => 'required|exists:menu_items,id',
                'quantity' => 'required|numeric|min:0.01',
                'store_id' => 'required|exists:stores,id',
            ]);

            $result = $this->stockDeductionService->simulateDeduction(
                $validated['menu_item_id'],
                $validated['quantity'],
                $validated['store_id']
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get theoretical consumption report
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTheoreticalConsumption(Request $request)
    {
        try {
            $storeId = $request->input('store_id', currentStoreId());
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $query = TheoreticalConsumption::with(['product', 'store'])
                ->where('store_id', $storeId);

            if ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('date', '<=', $dateTo);
            }

            $consumption = $query->orderBy('date', 'desc')
                ->orderBy('variance_percentage', 'desc')
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $consumption,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get variance report (high variance items)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getVarianceReport(Request $request)
    {
        try {
            $storeId = $request->input('store_id', currentStoreId());
            $threshold = $request->input('threshold', 5); // Default 5% variance threshold
            $dateFrom = $request->input('date_from');
            $dateTo = $request->input('date_to');

            $query = TheoreticalConsumption::with(['product', 'store'])
                ->where('store_id', $storeId)
                ->whereRaw('ABS(variance_percentage) > ?', [$threshold]);

            if ($dateFrom) {
                $query->where('date', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->where('date', '<=', $dateTo);
            }

            $highVarianceItems = $query->orderByRaw('ABS(variance_percentage) DESC')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'threshold' => $threshold,
                    'high_variance_items' => $highVarianceItems,
                    'total_items' => $highVarianceItems->count(),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
