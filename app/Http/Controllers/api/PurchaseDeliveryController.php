<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\PurchaseDeliveryResource;
use App\Services\Purchase\PurchaseDeliveryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class PurchaseDeliveryController extends BaseController
{
    public function __construct(
        private readonly PurchaseDeliveryService $deliveryService
    ) {}

    /**
     * Get all deliveries for a purchase order
     * 
     * @param int $purchaseOrderId
     * @return JsonResponse
     */
    public function indexByPurchaseOrder(int $purchaseOrderId): JsonResponse
    {
        try {
            $deliveries = $this->deliveryService->getDeliveriesForPurchaseOrder($purchaseOrderId);

            return response()->json([
                'deliveries' => PurchaseDeliveryResource::collection($deliveries),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error fetching deliveries: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Store a new delivery note
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_purchase_id' => 'required|exists:order_purchases,id',
            'delivery_date' => 'required|date',
            'received_by' => 'nullable|exists:users,id',
            'supplier_delivery_note' => 'nullable|string|max:255',
            'transport_company' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'vehicle_plate' => 'nullable|string|max:255',
            'delivery_note' => 'nullable|string',
            'quality_check_note' => 'nullable|string',
            'has_issues' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.order_purchase_item_id' => 'required|exists:order_purchase_items,id',
            'items.*.delivered_quantity' => 'required|integer|min:1',
            'items.*.accepted_quantity' => 'nullable|integer|min:0',
            'items.*.rejected_quantity' => 'nullable|integer|min:0',
            'items.*.rejection_reason' => 'nullable|string',
            'items.*.batch_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $delivery = $this->deliveryService->createDelivery($validator->validated());

            return response()->json([
                'delivery' => new PurchaseDeliveryResource($delivery),
                'message' => 'Delivery created successfully',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error creating delivery: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Display a specific delivery
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->findWithRelations($id, [
                'deliveryItems.product',
                'orderPurchase.supplier',
                'receivedBy',
                'store'
            ]);

            if (!$delivery) {
                return response()->json([
                    'message' => 'Delivery not found',
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'delivery' => new PurchaseDeliveryResource($delivery),
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error fetching delivery: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Validate a delivery (update stock)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function validate(int $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->validateDelivery($id);

            return response()->json([
                'delivery' => new PurchaseDeliveryResource($delivery),
                'message' => 'Delivery validated successfully. Stock has been updated.',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error validating delivery: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Cancel a delivery
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $delivery = $this->deliveryService->cancelDelivery($id);

            return response()->json([
                'delivery' => new PurchaseDeliveryResource($delivery),
                'message' => 'Delivery cancelled successfully',
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error cancelling delivery: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
