<?php

namespace App\Services\OrderSale;

use App\Enums\EnumPayementStatue;
use App\Models\Assurances;
use App\Models\OrderSale;
use App\Models\Payemnt;
use App\Enums\EnumOrderStatue;
use App\Repositories\Sale\SaleRepository;
use App\Services\Alert\AlertService;
use App\Services\Customer\CustomerService;
use App\Services\Payement\PayementService;
use App\Services\Sale\OrderSaleDetailService;
use App\Services\Setting\SettingService;
use App\Services\Stock\StockService;
use App\Services\Stock\StockDeductionService;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private CustomerService $customerService,
        private OrderSaleDetailService $orderSaleDetailService,
        private PayementService $payementService,
        private SettingService $settingService,
        private SaleRepository $saleRepository,
        private AlertService $alertService,
        private StockService $stockService,
        private StockDeductionService $stockDeductionService,
    ) {}
    public function findById(int $id)
    {
        return $this->saleRepository->find($id);
    }

    public function findwithRelations(int $id, array $relations)
    {
        return $this->saleRepository->findWith($id, OrderSale::COL_ID, $relations);
    }

    public function create(array $attributes): ?OrderSale
    {
        DB::beginTransaction();
        try {
            $storeId = currentStoreId();
            // Get count of orders created today and format as 00001, 00002, etc.
            $todayOrderCount = OrderSale::where(OrderSale::COL_STORE_ID, $storeId)
                ->whereDate('created_at', today())
                ->count();
            $orderNumber = str_pad($todayOrderCount + 1, 5, '0', STR_PAD_LEFT);


            // Step 5: Calculate total command from items and glass types with discount and advance
            $totalCommand = array_reduce($attributes['items'] ?? [], fn($sum, $item) => $sum + ($item['price'] * $item['qte']), 0);

            // Calculate Rest to Pay
            $discount = $attributes[OrderSale::COL_DISCOUNT] ?? 0;
            $advance = $attributes[OrderSale::COL_ADVANCE] ?? 0;
            $attributes[OrderSale::COL_TOTAL_COMMAND] = $totalCommand;
            $attributes[OrderSale::COL_REST_A_PAY] = max(0, $totalCommand - $advance - $discount);
            $attributes[OrderSale::COL_TOTAL_PAYMENT] = $advance;
            // Step 6: Determine state of order
            $attributes[OrderSale::COL_STATUS] = EnumPayementStatue::PAID->value;

            // Step 7: Create order
            $sale = OrderSale::create([
                OrderSale::COL_ORDER_NUMBER => $orderNumber,
                OrderSale::COL_ADVANCE => $attributes[OrderSale::COL_ADVANCE] ?? 0,
                OrderSale::COL_DISCOUNT => $attributes[OrderSale::COL_DISCOUNT] ?? 0,
                OrderSale::COL_TOTAL_COMMAND => $attributes[OrderSale::COL_TOTAL_COMMAND],
                OrderSale::COL_TOTAL_PAYMENT => $attributes[OrderSale::COL_TOTAL_PAYMENT] ?? 0,
                OrderSale::COL_REST_A_PAY => $attributes[OrderSale::COL_REST_A_PAY],
                OrderSale::COL_STATUS => $attributes[OrderSale::COL_STATUS],
                OrderSale::COL_IS_INVOICE => true,
                OrderSale::COL_STORE_ID => $storeId,
                OrderSale::COL_USER_ID => auth()->id(),
                OrderSale::COL_NOTE => $attributes['passport_data'] ?? null,
                OrderSale::COL_INVOICE_TOTAL => $attributes[OrderSale::COL_TOTAL_COMMAND],
            ]);



            // Step 9 & 10: Merge type glasses and add to order sale items, create detail of order
            $allItems =  $attributes['items'];

            foreach ($allItems as $item) {
                // Create order sale detail
                $this->orderSaleDetailService->create($item, $sale);

                // Step 11 & 12: Create movement and update stock of product for store (only for products)
                if (!empty($item['product_id'])) {
                    $this->stockService->processStoreProductMovement([
                        'product_id' => $item['product_id'],
                        'store_id' => $storeId,
                        'quantity' => $item['qte'],
                        'type' => 'sale',
                        'direction' => 'out',
                        'price' => $item['price'],
                        'unit_cost' => $item['price'],
                        'referenceable_type' => OrderSale::class,
                        'referenceable_id' => $sale->getId(),
                        'user_id' => auth()->id(),
                        'note' => "Sale order: {$orderNumber}",
                    ]);
                }
            }






            // Step 16: Check for product stock alerts after stock movements
            $this->checkProductStockAlertsAfterSale($storeId, $allItems);

            DB::commit();
            return $sale->load(['orderItems', 'user']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update(int $id, array $attributes): bool
    {
        $order = $this->saleRepository->find($id);
        if (!$order) {
            return false;
        }

        $order = $order->update($attributes);
        return $order;
    }

    public function updateToInvoice(int $id): bool
    {

        $invoiceNumber = $this->settingService->getNextSaleInvoiceNumber();
        // Increment the number in settings
        $order =  $this->update($id, [
            OrderSale::COL_IS_INVOICE => true,
            OrderSale::COL_ORDER_NUMBER => $invoiceNumber,
        ]);

        $this->settingService->incrementSaleInvoiceNumber();

        return $order;
    }

    /**
     * Cancel a sale order, reverse stock, and mark payments as voided.
     */
    public function cancel(int $orderId, ?string $reason = null): OrderSale
    {
        $order = $this->saleRepository->find($orderId);

        if (!$order instanceof OrderSale) {
            throw new \RuntimeException('Order not found.');
        }

        if ($order->getAttribute(OrderSale::COL_CANCELLED_AT) !== null) {
            throw new \RuntimeException('Order is already cancelled.');
        }

        DB::beginTransaction();
        try {
            // Reverse stock for all stockable product items
            foreach ($order->orderItems as $item) {
                if (!empty($item->product_id) && optional($item->product)->is_stockable) {
                    $this->stockService->processStoreProductMovement([
                        'product_id'          => $item->product_id,
                        'store_id'            => $order->getAttribute(OrderSale::COL_STORE_ID),
                        'quantity'            => $item->qte,
                        'type'                => 'adjustment',
                        'direction'           => 'in',
                        'price'               => $item->price,
                        'unit_cost'           => $item->price,
                        'referenceable_type'  => OrderSale::class,
                        'referenceable_id'    => $order->getId(),
                        'user_id'             => auth()->id(),
                        'note'                => "Cancellation of order: {$order->order_number}",
                    ]);
                }

                // Mark order item as cancelled
                $item->update([
                    \App\Models\OrderItems::COL_IS_CANCELLED => true,
                ]);
            }

            // Mark records
            $order->update([
                OrderSale::COL_CANCELLED_AT => now(),
                OrderSale::COL_CANCELLED_BY => auth()->id(),
                OrderSale::COL_CANCEL_REASON => $reason,
            ]);

            DB::commit();
            return $order->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function addPaymentToOrder(int $orderId, float $amount): Payemnt
    {
        $order = $this->findById($orderId);

        $payement = $this->payementService->create($amount, $order);

        // Recalculate order totals
        $totalPayments = $order->payments()->sum('amount');
        $discount = $order->getAttribute(OrderSale::COL_DISCOUNT) ?? 0;
        $restToPay = max(0, $order->getAttribute(OrderSale::COL_TOTAL_COMMAND) - $totalPayments - $discount);

        // Update order status
        $status = match (true) {
            $restToPay == 0 => EnumPayementStatue::PAID->value,
            $totalPayments > 0 => EnumPayementStatue::AVANCE->value,
            default => EnumPayementStatue::UNPAID->value
        };

        $this->update($orderId, [
            OrderSale::COL_TOTAL_PAYMENT => $totalPayments,
            OrderSale::COL_REST_A_PAY => $restToPay,
            OrderSale::COL_STATUS => $status,
        ]);

        return $payement;
    }

    /**
     * Resolve customer inactivity alerts when customer makes a purchase
     */
    private function resolveCustomerInactivityAlerts($customer): void
    {
        if (!$customer) return;

        // Find any unresolved customer inactivity alerts for this customer
        $inactiveAlerts = \App\Models\Alert::where('customer_id', $customer->id)
            ->whereIn('type', [
                \App\Models\Alert::TYPE_CUSTOMER_INACTIVE_CHILD,
                \App\Models\Alert::TYPE_CUSTOMER_INACTIVE_MINOR,
                \App\Models\Alert::TYPE_CUSTOMER_INACTIVE_ADULT
            ])
            ->unresolved()
            ->get();

        foreach ($inactiveAlerts as $alert) {
            // Mark the alert as resolved since customer just made a purchase
            $alert->markAsResolved(auth()->user());
        }
    }

    /**
     * Check for product stock alerts after sale stock movements
     */
    private function checkProductStockAlertsAfterSale(int $storeId, array $saleItems): void
    {
        $productIds = [];

        // Collect all product IDs that were sold
        foreach ($saleItems as $item) {
            if (!empty($item['product_id'])) {
                $productIds[] = $item['product_id'];
            }
        }

        if (empty($productIds)) return;

        // Remove duplicates
        $productIds = array_unique($productIds);

        // Check stock alerts only for these specific products
        $this->alertService->generateProductStockAlertsForProducts($productIds, $storeId);
    }

    /**
     * Create a restaurant order with menu items
     * This is specifically for restaurant/menu-based sales (not direct product sales)
     * 
     * @param array $attributes - Must include 'menu_items' array with menu_item_id and quantity
     * @return OrderSale|null
     * @throws \Exception
     */
    public function createRestaurantOrder(array $attributes): ?OrderSale
    {
        DB::beginTransaction();
        try {
            $storeId = currentStoreId();

            // Get count of orders created today for this store
            $todayOrderCount = OrderSale::where(OrderSale::COL_STORE_ID, $storeId)
                ->whereDate('created_at', today())
                ->count();
            $orderNumber = str_pad($todayOrderCount + 1, 5, '0', STR_PAD_LEFT);

            // Calculate total from menu items
            $totalCommand = 0;
            $menuItemsData = [];

            foreach ($attributes['menu_items'] ?? [] as $menuItemRequest) {
                $menuItem = \App\Models\MenuItem::with(['recipe', 'product'])->findOrFail($menuItemRequest['menu_item_id']);

                if (!$menuItem->is_active || !$menuItem->is_available) {
                    throw new \Exception("Menu item '{$menuItem->name}' is not available");
                }

                $quantity = $menuItemRequest['quantity'] ?? 1;
                $itemTotal = $menuItem->price * $quantity;
                $totalCommand += $itemTotal;

                $menuItemsData[] = [
                    'menu_item' => $menuItem,
                    'quantity' => $quantity,
                    'price' => $menuItem->price,
                    'cost' => $menuItem->cost,
                    'total' => $itemTotal,
                ];
            }

            // Calculate Rest to Pay
            $discount = $attributes[OrderSale::COL_DISCOUNT] ?? 0;
            $advance = $attributes[OrderSale::COL_ADVANCE] ?? 0;
            $attributes[OrderSale::COL_TOTAL_COMMAND] = $totalCommand;
            $attributes[OrderSale::COL_REST_A_PAY] = max(0, $totalCommand - $advance - $discount);
            $attributes[OrderSale::COL_TOTAL_PAYMENT] = $advance;

            // Determine state of order
            $attributes[OrderSale::COL_STATUS] = $attributes[OrderSale::COL_REST_A_PAY] == 0
                ? EnumPayementStatue::PAID->value
                : ($advance > 0 ? EnumPayementStatue::AVANCE->value : EnumPayementStatue::UNPAID->value);

            // Create order
            $sale = OrderSale::create([
                OrderSale::COL_ORDER_NUMBER => $orderNumber,
                OrderSale::COL_ADVANCE => $attributes[OrderSale::COL_ADVANCE] ?? 0,
                OrderSale::COL_DISCOUNT => $attributes[OrderSale::COL_DISCOUNT] ?? 0,
                OrderSale::COL_TOTAL_COMMAND => $attributes[OrderSale::COL_TOTAL_COMMAND],
                OrderSale::COL_TOTAL_PAYMENT => $attributes[OrderSale::COL_TOTAL_PAYMENT] ?? 0,
                OrderSale::COL_REST_A_PAY => $attributes[OrderSale::COL_REST_A_PAY],
                OrderSale::COL_STATUS => $attributes[OrderSale::COL_STATUS],
                OrderSale::COL_IS_INVOICE => true,
                OrderSale::COL_STORE_ID => $storeId,
                OrderSale::COL_USER_ID => auth()->id(),
                OrderSale::COL_NOTE => $attributes['note'] ?? null,
                OrderSale::COL_INVOICE_TOTAL => $attributes[OrderSale::COL_TOTAL_COMMAND],
                OrderSale::COL_CUSTOMER_ID => $attributes['customer_id'] ?? null,
            ]);

            // Create order items for menu items
            foreach ($menuItemsData as $itemData) {
                \App\Models\OrderItems::create([
                    \App\Models\OrderItems::COL_NAME => $itemData['menu_item']->name,
                    \App\Models\OrderItems::COL_ORDER_ID => $sale->getId(),
                    \App\Models\OrderItems::COL_STORE_ID => $storeId,
                    \App\Models\OrderItems::COL_PRODUCT_ID => $itemData['menu_item']->id,
                    \App\Models\OrderItems::COL_PRODUCT_TYPE => \App\Models\MenuItem::class,
                    \App\Models\OrderItems::COL_PRICE => $itemData['price'],
                    \App\Models\OrderItems::COL_QTE => $itemData['quantity'],
                    \App\Models\OrderItems::COL_TOTAL => $itemData['total'],
                    \App\Models\OrderItems::COL_INVOICE_PRICE => $itemData['price'],
                ]);
            }

            DB::commit();
            return $sale->load(['orderItems.product', 'user', 'customer']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create restaurant sale from frontend menu items
     * 
     * @param array $attributes - Contains 'items' array with full item data, 'total_command', 'total_payment'
     * @return OrderSale|null
     * @throws \Exception
     */
    public function sellMenuItems(array $attributes): ?OrderSale
    {
        DB::beginTransaction();
        try {
            $storeId = currentStoreId();

            // Get count of orders created today for this store
            $todayOrderCount = OrderSale::where(OrderSale::COL_STORE_ID, $storeId)
                ->whereDate('created_at', today())
                ->count();
            $orderNumber = str_pad($todayOrderCount + 1, 5, '0', STR_PAD_LEFT);

            // Validate items and prepare order items data
            $menuItemsData = [];
            $calculatedTotal = 0;

            foreach ($attributes['items'] ?? [] as $item) {
                // Fetch menu item from database to verify it exists and is available
                $menuItem = \App\Models\MenuItem::with(['recipe', 'product'])->find($item['id']);

                if (!$menuItem) {
                    throw new \Exception("Menu item with ID {$item['id']} not found");
                }

                if (!$menuItem->is_active || !$menuItem->is_available) {
                    throw new \Exception("Menu item '{$menuItem->name}' is not available");
                }

                // Validate price matches (security check to prevent price manipulation)
                if (abs($menuItem->price - $item['price']) > 0.01) {
                    throw new \Exception("Price mismatch for menu item '{$menuItem->name}'");
                }

                $quantity = $item['qte'] ?? $item['quantity'] ?? 1;
                $itemTotal = $menuItem->price * $quantity;
                $calculatedTotal += $itemTotal;

                $menuItemsData[] = [
                    'menu_item' => $menuItem,
                    'quantity' => $quantity,
                    'price' => $menuItem->price,
                    'cost' => $menuItem->cost,
                    'total' => $itemTotal,
                    'item_type' => $item['item_type'] ?? $menuItem->item_type,
                ];
            }

            // Note: Stock deduction will happen after order creation
            // to ensure we have a valid order reference for stock movements

            // Validate total (allow small floating point differences)
            $totalCommand = $attributes['total_command'] ?? $calculatedTotal;
            if (abs($totalCommand - $calculatedTotal) > 0.05) {
                throw new \Exception("Total amount mismatch. Expected: {$calculatedTotal}, Received: {$totalCommand}");
            }

            // Calculate payments
            $totalPayment = $attributes['total_payment'] ?? 0;
            $discount = $attributes['discount'] ?? 0;
            $advance = $attributes['advance'] ?? $totalPayment;
            $restToPay = max(0, $totalCommand - $advance - $discount);

            // Determine order status
            $status = $restToPay == 0
                ? EnumPayementStatue::PAID->value
                : ($advance > 0 ? EnumPayementStatue::AVANCE->value : EnumPayementStatue::UNPAID->value);

            // Create order
            $sale = OrderSale::create([
                OrderSale::COL_ORDER_NUMBER => $orderNumber,
                OrderSale::COL_ADVANCE => $advance,
                OrderSale::COL_DISCOUNT => $discount,
                OrderSale::COL_TOTAL_COMMAND => $totalCommand,
                OrderSale::COL_TOTAL_PAYMENT => $totalPayment,
                OrderSale::COL_REST_A_PAY => $restToPay,
                OrderSale::COL_STATUS => $status,
                OrderSale::COL_IS_INVOICE => true,
                OrderSale::COL_STORE_ID => $storeId,
                OrderSale::COL_USER_ID => auth()->id(),
                OrderSale::COL_NOTE => $attributes['note'] ?? null,
                OrderSale::COL_INVOICE_TOTAL => $totalCommand,
                OrderSale::COL_CUSTOMER_ID => $attributes['customer_id'] ?? null,
            ]);

            // Create order items and handle stock deduction
            foreach ($menuItemsData as $itemData) {
                $menuItem = $itemData['menu_item'];
                $quantity = $itemData['quantity'];

                \App\Models\OrderItems::create([
                    \App\Models\OrderItems::COL_NAME => $menuItem->name,
                    \App\Models\OrderItems::COL_ORDER_ID => $sale->getId(),
                    \App\Models\OrderItems::COL_STORE_ID => $storeId,
                    \App\Models\OrderItems::COL_PRODUCT_ID => $menuItem->id,
                    \App\Models\OrderItems::COL_PRODUCT_TYPE => \App\Models\MenuItem::class,
                    \App\Models\OrderItems::COL_PRICE => $itemData['price'],
                    \App\Models\OrderItems::COL_QTE => $quantity,
                    \App\Models\OrderItems::COL_TOTAL => $itemData['total'],
                    \App\Models\OrderItems::COL_INVOICE_PRICE => $itemData['price'],
                ]);

                // Use StockDeductionService for proper stock management
                // This handles: recipe ingredients, unit conversion, waste percentage, 
                // theoretical consumption, and proper error handling
                // Note: Only deduct stock for recipe-based and product-based items
                // Simple items (like delivery fees) don't have stock tracking
                if (in_array($menuItem->item_type, [\App\Models\MenuItem::ITEM_TYPE_RECIPE, \App\Models\MenuItem::ITEM_TYPE_PRODUCT])) {
                    $this->stockDeductionService->deductMenuItemStock(
                        $menuItem->id,
                        $quantity,
                        $storeId,
                        auth()->id(),
                        $sale->getId()
                    );
                }
            }

            // Record payment if advance was made
            if ($advance > 0) {
                Payemnt::create([
                    Payemnt::COL_ORDER_ID => $sale->getId(),
                    Payemnt::COL_STORE_ID => $storeId,
                    Payemnt::COL_USER_ID => auth()->id(),
                    Payemnt::COL_AMOUNT => $advance,
                    Payemnt::COL_MODE_PAYEMNT_ID => $attributes['payment_method_id'] ?? null,
                    Payemnt::COL_NOTE => "Initial payment for order #{$orderNumber}",
                ]);
            }

            DB::commit();
            return $sale->load(['orderItems.product', 'user', 'customer', 'payments']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
