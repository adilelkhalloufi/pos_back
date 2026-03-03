<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Services\Ajustement\AjustementService;
use Illuminate\Http\Request;

class AjustementController extends BaseController
{
    private AjustementService $ajustementService;

    public function __construct(AjustementService $ajustementService)
    {
        parent::__construct();
        $this->ajustementService = $ajustementService;
    }

    /**
     * Display a listing of adjustments
     */
    public function index(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $ajustements = $this->ajustementService->getByStore($storeId);

        return response()->json($ajustements, 200);
    }

    /**
     * Store a newly created adjustment
     */
    public function store(Request $request)
    {
 
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.type' => 'required|in:increase,decrease',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.note' => 'nullable|string',
            'reason' => 'required',
            'note' => 'nullable|string',
            'meta' => 'nullable|array',
            'store_id' => 'nullable|exists:stores,id',
        ]);

       

        try {
            $ajustement = $this->ajustementService->create($validated);

            return response()->json([
                'message' => 'Adjustment created successfully',
                'data' => $ajustement
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create adjustment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified adjustment
     */
    public function show($id)
    {
        try {
            $ajustement = $this->ajustementService->findById($id);

            if (!$ajustement) {
                return response()->json(['error' => 'Adjustment not found'], 404);
            }

            return response()->json($ajustement, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch adjustment',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function approve($id)
    {
        try {
            $ajustement = $this->ajustementService->updateStatus($id, 'completed');

            return response()->json([
                'message' => 'Adjustment approved successfully',
                'data' => $ajustement
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to approve adjustment',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
