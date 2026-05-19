<?php

namespace App\Observers;

use App\Models\OrderSale;
use App\Models\MenuItem;
use App\Services\CacheService;
use App\Services\Stock\StockDeductionService;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderSaleObserver
{
    protected CacheService $cacheService;
    protected StockDeductionService $stockDeductionService;

    public function __construct(
        CacheService $cacheService,
        StockDeductionService $stockDeductionService
    ) {
        $this->cacheService = $cacheService;
        $this->stockDeductionService = $stockDeductionService;
    }

    /**
     * Handle the OrderSale "created" event.
     * Clear dashboard cache and deduct stock for menu items when a new order is created.
     */
    public function created(OrderSale $orderSale): void
    {
        $this->clearDashboardCache($orderSale);
        $this->deductStockForOrder($orderSale);
    }

    /**
     * Handle the OrderSale "updated" event.
     * Clear dashboard cache when an order is updated.
     */
    public function updated(OrderSale $orderSale): void
    {
        $this->clearDashboardCache($orderSale);
    }

    /**
     * Handle the OrderSale "deleted" event.
     * Clear dashboard cache when an order is deleted.
     */
    public function deleted(OrderSale $orderSale): void
    {
        $this->clearDashboardCache($orderSale);
    }

    /**
     * Clear the dashboard cache for the store associated with the order
     */
    private function clearDashboardCache(OrderSale $orderSale): void
    {
        $storeId = $orderSale->getAttribute(OrderSale::COL_STORE_ID);

        if ($storeId) {
            // Clear dashboard cache for this specific store
            $cacheKey = "store_{$storeId}_dashboard";
            $this->cacheService->forgetCache($cacheKey);

            // You can add more cache keys to clear if needed
            // $this->cacheService->forgetCache("store_{$storeId}_sales_summary");
            // $this->cacheService->forgetCache("store_{$storeId}_recent_orders");
        }
    }

    /**
     * Deduct stock for menu items in the order
     * 
     * @param OrderSale $orderSale
     */
    private function deductStockForOrder(OrderSale $orderSale): void
    {
        try {
            // Load order items with relationships
            $orderSale->load('orderItems');

            foreach ($orderSale->orderItems as $orderItem) {
                // Check if the order item is a menu item
                if ($orderItem->product_type === MenuItem::class && $orderItem->product_id) {
                    try {
                        // Deduct stock for this menu item
                        $this->stockDeductionService->deductMenuItemStock(
                            menuItemId: $orderItem->product_id,
                            quantity: $orderItem->qte ?? 1,
                            storeId: $orderSale->store_id,
                            userId: $orderSale->user_id,
                            orderSaleId: $orderSale->id
                        );

                        Log::info("Stock deducted for menu item", [
                            'order_id' => $orderSale->id,
                            'menu_item_id' => $orderItem->product_id,
                            'quantity' => $orderItem->qte,
                        ]);
                    } catch (Exception $e) {
                        // Log the error but don't fail the entire order
                        Log::error("Failed to deduct stock for menu item", [
                            'order_id' => $orderSale->id,
                            'menu_item_id' => $orderItem->product_id,
                            'error' => $e->getMessage(),
                        ]);

                        // Optionally, you could throw the exception to prevent order creation
                        // if stock deduction is critical: throw $e;
                    }
                }
            }
        } catch (Exception $e) {
            Log::error("Failed to process stock deduction for order", [
                'order_id' => $orderSale->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}