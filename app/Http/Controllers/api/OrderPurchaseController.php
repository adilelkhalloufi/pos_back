<?php

namespace App\Http\Controllers\api;

use App\Enums\LogParametersList;
use App\Http\Controllers\BaseController;
use App\Http\Requests\PurchaseRequest;
use App\Http\Resources\OrderPurchaseResource;
use App\Services\Purchase\PurchaseService;
use App\Traits\AppliesDateFilters;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrderPurchaseController extends BaseController
{
    use AppliesDateFilters;

    public function __construct(
        private readonly PurchaseService $purchaseService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $this->the_store()
            ->purchases()
            ->getQuery()
            ->with(['supplier', 'orderItems.product']);

        // Apply date filter if provided, otherwise get latest 25 records
        $query = $this->applyDateFilter($query, $request, 25);

        $orders = $query->get();

        return response()->json(OrderPurchaseResource::collection($orders), Response::HTTP_OK);
    }


    public function store(PurchaseRequest $request)
    {
        $validated = $request->validated();
        try {
            $order = $this->purchaseService->create($validated);
        } catch (Exception $e) {

            // do logs here
            $this->logger->error(
                'error occurred while registering a purchase order',
                [
                    LogParametersList::FEATURE => LogParametersList::CREATE->value,
                    LogParametersList::ERROR_MESSAGE => $e->getMessage(),
                    LogParametersList::ERROR_TRACE => $e->getTraceAsString(),
                ]
            );
            return response()->json([
                'message' => 'Error creating purchase order: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }


        return  response()->json([
            'order' => new OrderPurchaseResource($order),
            'message' => 'Order created successfully',
        ], Response::HTTP_CREATED);
    }


    public function approve(int $purchaseId)
    {
        try {
            $purchase = $this->purchaseService->approvePurchase($purchaseId);

            return response()->json([
                'purchase' => new OrderPurchaseResource($purchase),
                'message' => 'Purchase order approved successfully',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error approving purchase order: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function cancel(int $purchaseId)
    {
        try {
            $purchase = $this->purchaseService->cancelPurchase($purchaseId);

            return response()->json([
                'purchase' => new OrderPurchaseResource($purchase),
                'message' => 'Purchase order canceled successfully',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error canceling purchase order: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }


    public function show(int $id)
    {
        $purchase = $this->purchaseService->findwithRelations($id, ['orderItems.product', 'supplier']);

        if (!$purchase) {
            return response()->json([
                'message' => 'Purchase order not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json(new OrderPurchaseResource($purchase), Response::HTTP_OK);
    }
}
