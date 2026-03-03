<?php

namespace App\Observers;

use App\Models\OrderSale;
use App\Services\CacheService;

class OrderSaleObserver
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the OrderSale "created" event.
     * Clear dashboard cache when a new order is created.
     */
    public function created(OrderSale $orderSale): void
    {
        $this->clearDashboardCache($orderSale);
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
}