<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Services\Transfert\TransfertService;
use Illuminate\Http\Request;

class TransfertController extends BaseController
{
 
    public function __construct(private TransfertService $transfertService)
    {
        parent::__construct();
     }

    /**
     * Display a listing of transfers
     */
    public function index(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $type = $request->query('type'); // 'source', 'target', or null for both
        $transferts = $this->transfertService->getByStore($storeId, $type);

        return response()->json($transferts, 200);
    }

    /**
     * Store a newly created transfer
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'source_store_id' => 'required|exists:stores,id',
            'target_store_id' => 'required|exists:stores,id|different:source_store_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.note' => 'nullable|string',
            'note' => 'nullable|string',
            'meta' => 'nullable|array',
        ]);

        try {
            $transfert = $this->transfertService->create($validated);
            return response()->json([
                'message' => 'Transfer created successfully',
                'data' => $transfert
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create transfer',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified transfer
     */
    public function show($id)
    {
        try {
            $transfert = $this->transfertService->findById($id);

            if (!$transfert) {
                return response()->json(['error' => 'Transfer not found'], 404);
            }

            return response()->json($transfert, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch transfer',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a transfer
     */
    public function approve($id)
    {
        try {
            $transfert = $this->transfertService->updateStatus($id, 'completed');

            return response()->json([
                'message' => 'Transfer approved successfully',
                'data' => $transfert
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to approve transfer',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
