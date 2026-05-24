<?php

namespace App\Http\Controllers\api;

use App\Enums\LogParametersList;
use App\Http\Controllers\BaseController;
use App\Http\Requests\SaleRequest;
use App\Http\Resources\OrderResource;
use App\Models\OrderSale;
use App\Services\OrderSale\SaleService;
use App\Traits\AppliesDateFilters;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class OrderSaleController extends BaseController
{
    use AppliesDateFilters;

    public function __construct(
        private readonly SaleService $saleService,
        // private readonly PayementService $payementService,
    ) {
        parent::__construct();
    }


    public function index(Request $request)
    {


        $store = $this->the_store();


        $query = $store
            ->sales()
            ->getQuery()
            ->with(['customer', 'orderItems.product']);

        // Exclude cancelled orders by default unless explicitly requested
        if (!$request->has('include_cancelled') || !$request->boolean('include_cancelled')) {
            $query->notCancelled();
        }

        // Filter to show only cancelled orders if requested
        if ($request->has('only_cancelled') && $request->boolean('only_cancelled')) {
            $query->cancelled();
        }

        // Filter by invoice if invoice parameter is present
        if ($request->has('invoice')) {
            $query->where(OrderSale::COL_IS_INVOICE, true);
        }
        if ($request->has('status')) {
            $query->where(OrderSale::COL_STATUS, $request->input('status'));
        }

        $query = $this->applyDateFilter($query, $request, 1000);
        $orders = $query->get();
        return response()->json(OrderResource::collection($orders), Response::HTTP_OK);
    }


    public function store(SaleRequest $request)
    {
        $validated = $request->validated();

        try {
            $order = $this->saleService->create($validated);
        } catch (\Exception $e) {

            // do logs here
            $this->logger->error(
                'error occurred while registering a sale order',
                [
                    LogParametersList::STORE_ID->value => $this->the_store(),
                    LogParametersList::FEATURE->value => LogParametersList::CREATE->value,
                    LogParametersList::ERROR_MESSAGE->value => $e->getMessage(),
                    LogParametersList::ERROR_TRACE->value => $e->getTraceAsString(),
                    LogParametersList::REQUEST->value => $request->all(),
                ]
            );
            return response()->json([
                'message' => 'Error creating sale order: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
        return  response()->json([
            'order' => new OrderResource($order),
            'message' => 'Order created successfully',
        ], Response::HTTP_CREATED);
    }

    /**
     * Create a restaurant order with menu items
     * Specific endpoint for restaurant operations
     */
    public function createRestaurantOrder(Request $request)
    {
        $validated = $request->validate([
            'menu_items' => 'required|array|min:1',
            'menu_items.*.menu_item_id' => 'required|exists:menu_items,id',
            'menu_items.*.quantity' => 'required|numeric|min:0.01',
            'customer_id' => 'nullable|exists:customers,id',
            'discount' => 'nullable|numeric|min:0',
            'advance' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        try {
            $order = $this->saleService->createRestaurantOrder($validated);

            return response()->json([
                'order' => new OrderResource($order),
                'message' => 'Restaurant order created successfully',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error(
                'error occurred while creating restaurant order',
                [
                    LogParametersList::STORE_ID->value => $this->the_store(),
                    LogParametersList::FEATURE->value => LogParametersList::CREATE->value,
                    LogParametersList::ERROR_MESSAGE->value => $e->getMessage(),
                    LogParametersList::ERROR_TRACE->value => $e->getTraceAsString(),
                    LogParametersList::REQUEST->value => $request->all(),
                ]
            );
            return response()->json([
                'message' => 'Error creating restaurant order: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Sell menu items - endpoint for POS/restaurant frontend
     * Accepts full item data from frontend
     */
    public function sellMenuItems(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|integer|exists:menu_items,id',
            'items.*.name' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.cost' => 'nullable|numeric|min:0',
            'items.*.qte' => 'required|numeric|min:0.01',
            'items.*.item_type' => 'nullable|string',
            'items.*.image' => 'nullable|string',
            'items.*.description' => 'nullable|string',
            'items.*.category_id' => 'nullable|integer',
            'items.*.stock' => 'nullable|numeric',
            'items.*.preparation_time_minutes' => 'nullable|integer',
            'total_command' => 'required|numeric|min:0',
            'total_payment' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'advance' => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method_id' => 'nullable|exists:payement_methods,id',
            'note' => 'nullable|string',
        ]);

        try {
            $order = $this->saleService->sellMenuItems($validated);

            return response()->json([
                'success' => true,
                'order' => new OrderResource($order),
                'message' => 'Menu items sold successfully',
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logger->error(
                'error occurred while selling menu items',
                [
                    LogParametersList::STORE_ID->value => $this->the_store(),
                    LogParametersList::FEATURE->value => LogParametersList::CREATE->value,
                    LogParametersList::ERROR_MESSAGE->value => $e->getMessage(),
                    LogParametersList::ERROR_TRACE->value => $e->getTraceAsString(),
                    LogParametersList::REQUEST->value => $request->all(),
                ]
            );
            return response()->json([
                'success' => false,
                'message' => 'Error selling menu items: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function addPaymentToOrder(Request $request, $id)
    {


        $validatedData = $request->validate([
            'amount' => 'required|numeric',
        ]);

        try {


            $payment = $this->saleService->addPaymentToOrder(
                $id,
                $validatedData['amount']
            );


            return response()->json([
                'message' => 'Payment added successfully',
                'payment' => $payment
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Log the error
            $this->logger->error(
                'error occurred while adding payment to sale order',
                [
                    LogParametersList::STORE_ID->value => $this->the_store(),
                    LogParametersList::FEATURE->value => LogParametersList::CREATE->value,
                    LogParametersList::ERROR_MESSAGE->value => $e->getMessage(),
                    LogParametersList::ERROR_TRACE->value => $e->getTraceAsString(),
                    LogParametersList::REQUEST->value => $request->all(),
                ]
            );
            return response()->json([
                'message' => 'Error adding payment to sale order: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateToInvoice(Request $request, $id)
    {

        $order = $this->saleService->updateToInvoice($id);

        return response()->json([
            'message' => 'Order updated to invoice successfully',
            'order' => $order
        ], Response::HTTP_OK);
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $order = $this->saleService->cancel((int) $id, $request->input('reason'));
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error cancelling order: ' . $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'message' => 'Order cancelled successfully.',
            'order'   => new OrderResource($order),
        ], Response::HTTP_OK);
    }

    public function getCancelled(Request $request)
    {
        $store = $this->the_store();

        $query = $store
            ->sales()
            ->getQuery()
            ->with(['customer', 'orderItems.product', 'cancelledBy'])
            ->cancelled();

        // Filter by invoice if invoice parameter is present
        if ($request->has('invoice')) {
            $query->where(OrderSale::COL_IS_INVOICE, true);
        }

        $query = $this->applyDateFilter($query, $request, 1000);
        $orders = $query->get();

        return response()->json(OrderResource::collection($orders), Response::HTTP_OK);
    }
}
