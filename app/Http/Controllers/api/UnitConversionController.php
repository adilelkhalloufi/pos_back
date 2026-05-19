<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Services\UnitConversion\ConversionService;
use App\Models\UnitConversion;
use Exception;

class UnitConversionController
{
    protected $conversionService;

    public function __construct(ConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Get all conversions for a store
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $storeId = $request->input('store_id', currentStoreId());
            $includeGlobal = $request->boolean('include_global', true);

            $conversions = UnitConversion::with(['fromUnit', 'toUnit', 'store'])
                ->when($includeGlobal, function ($query) use ($storeId) {
                    return $query->where(function ($q) use ($storeId) {
                        $q->where('store_id', $storeId)
                          ->orWhereNull('store_id');
                    });
                }, function ($query) use ($storeId) {
                    return $query->where('store_id', $storeId);
                })
                ->orderBy('from_unit_id')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $conversions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new conversion rule
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'from_unit_id' => 'required|exists:units,id',
                'to_unit_id' => 'required|exists:units,id|different:from_unit_id',
                'conversion_factor' => 'required|numeric|min:0.000001',
                'store_id' => 'nullable|exists:stores,id',
            ]);

            $storeId = $validated['store_id'] ?? null;

            $conversion = $this->conversionService->createConversion(
                $validated['from_unit_id'],
                $validated['to_unit_id'],
                $validated['conversion_factor'],
                $storeId
            );

            return response()->json([
                'success' => true,
                'message' => 'Conversion rule created successfully',
                'data' => $conversion->load(['fromUnit', 'toUnit', 'store']),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update a conversion rule
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'conversion_factor' => 'required|numeric|min:0.000001',
            ]);

            $conversion = UnitConversion::findOrFail($id);
            $conversion->conversion_factor = $validated['conversion_factor'];
            $conversion->save();

            // Clear cache
            $cacheKey = "unit_conversion_{$conversion->from_unit_id}_{$conversion->to_unit_id}_" . ($conversion->store_id ?? 'global');
            \Illuminate\Support\Facades\Cache::forget($cacheKey);

            return response()->json([
                'success' => true,
                'message' => 'Conversion rule updated successfully',
                'data' => $conversion->load(['fromUnit', 'toUnit', 'store']),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Delete a conversion rule
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $result = $this->conversionService->deleteConversion($id);

            return response()->json([
                'success' => true,
                'message' => 'Conversion rule deleted successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Convert a quantity from one unit to another
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function convert(Request $request)
    {
        try {
            $validated = $request->validate([
                'quantity' => 'required|numeric|min:0',
                'from_unit_id' => 'required|exists:units,id',
                'to_unit_id' => 'required|exists:units,id',
                'store_id' => 'nullable|exists:stores,id',
            ]);

            $convertedQuantity = $this->conversionService->convert(
                $validated['quantity'],
                $validated['from_unit_id'],
                $validated['to_unit_id'],
                $validated['store_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'original_quantity' => $validated['quantity'],
                    'from_unit_id' => $validated['from_unit_id'],
                    'to_unit_id' => $validated['to_unit_id'],
                    'converted_quantity' => $convertedQuantity,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create standard unit conversions
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createStandard(Request $request)
    {
        try {
            $storeId = $request->input('store_id', null);

            $conversions = $this->conversionService->createStandardConversions($storeId);

            return response()->json([
                'success' => true,
                'message' => 'Standard conversions created successfully',
                'data' => $conversions,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
