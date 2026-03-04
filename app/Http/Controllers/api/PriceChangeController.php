<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PriceChangeRequest;
use App\Models\PriceChangeLog;
use App\Services\PriceChange\PriceChangeService;
use Illuminate\Http\Response;

class PriceChangeController extends BaseController
{
    public function __construct(private readonly PriceChangeService $priceChangeService)
    {
        parent::__construct();
    }

    /**
     * POST /price-changes
     * Apply a batch price change to one or many products.
     */
    public function store(PriceChangeRequest $request)
    {
        $data = $request->validated();

        try {
            $logs = $this->priceChangeService->applyBatch(
                $data['products'],
                $data,
                $data['effective_date'] ?? null,
                $data['reason'] ?? null
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json([
            'message' => 'Prices updated successfully.',
            'changes' => $logs,
        ], Response::HTTP_OK);
    }

    /**
     * GET /price-changes/{product}
     * History of price changes for a product.
     */
    public function history(int $productId)
    {
        $history = $this->priceChangeService->history($productId);
        return response()->json($history, Response::HTTP_OK);
    }
}
