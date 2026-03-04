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
}
