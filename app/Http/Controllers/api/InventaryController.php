<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Services\Inventary\InventaryService;
use Illuminate\Http\Request;

class InventaryController extends BaseController
{
    private InventaryService $inventaryService;

    public function __construct(InventaryService $inventaryService)
    {
        parent::__construct();
        $this->inventaryService = $inventaryService;
    }

    /**
     * Display a listing of inventories
     */
    public function index(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $inventaries = $this->inventaryService->getByStore($storeId);

        return response()->json($inventaries, 200);
    }

    /**
     * Store a newly created inventory
     */
    public function store(Request $request)
    {
        $storeId = $this->storeId();

        $validated = $request->validate([
            'note' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        $validated['store_id'] = $storeId;

        try {
            $inventary = $this->inventaryService->create($validated);

            return response()->json([
                'message' => 'Inventory created successfully',
                'data' => $inventary
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create inventory',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified inventory
     */
    public function show($id)
    {
        try {
            $inventary = $this->inventaryService->findById($id);

            if (!$inventary) {
                return response()->json(['error' => 'Inventory not found'], 404);
            }

            return response()->json($inventary, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch inventory',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start an inventory
     */
    public function start($id)
    {
        try {
            $inventary = $this->inventaryService->start($id);

            return response()->json([
                'message' => 'Inventory started successfully',
                'data' => $inventary
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to start inventory',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an inventory item
     */
    public function updateItem(Request $request, $id, $itemId)
    {
        $validated = $request->validate([
            'actual_quantity' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        try {
            $item = $this->inventaryService->updateItem($id, $itemId, $validated);

            return response()->json([
                'message' => 'Inventory item updated successfully',
                'data' => $item
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update inventory item',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete an inventory
     */
    public function complete(Request $request, $id)
    {
        $validated = $request->validate([
            'apply_adjustments' => 'nullable|boolean',
        ]);

        try {
            $inventary = $this->inventaryService->complete(
                $id,
                $validated['apply_adjustments'] ?? true
            );

            return response()->json([
                'message' => 'Inventory completed successfully',
                'data' => $inventary
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to complete inventory',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an inventory
     */
    public function cancel($id)
    {
        try {
            $inventary = $this->inventaryService->cancel($id);

            return response()->json([
                'message' => 'Inventory cancelled successfully',
                'data' => $inventary
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to cancel inventory',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified inventory
     */
    public function destroy($id)
    {
        try {
            $inventary = $this->inventaryService->findById($id);

            if (!$inventary) {
                return response()->json(['error' => 'Inventory not found'], 404);
            }

            // Only allow deletion of pending inventories
            if ($inventary->status !== 'pending') {
                return response()->json([
                    'error' => 'Only pending inventories can be deleted'
                ], 400);
            }

            $inventary->delete();

            return response()->json([
                'message' => 'Inventory deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete inventory',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
