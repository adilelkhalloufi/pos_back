<?php

namespace App\Services\Alert;

use App\Models\Alert;
use App\Models\Customer;
use App\Models\StoreProducts;
use Illuminate\Support\Collection;

class AlertService
{
    /**
     * Generate alerts for inactive customers
     */
    public function generateCustomerAlerts(?int $storeId = null): int
    {
        $count = 0;

        // Get customers who haven't had orders recently
        $customers = Customer::query()
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->whereNotNull('birthday')
            ->get();

        foreach ($customers as $customer) {
            $age = $customer->birthday ? now()->diffInYears($customer->birthday) : null;

            if (!$age) continue;

            $lastOrderDate = $customer->last_order_date;
            $daysSinceLastOrder = $lastOrderDate ? now()->diffInDays($lastOrderDate) : null;

            // Skip if we don't have order history
            if (!$daysSinceLastOrder) continue;

            $alertType = null;

            // Check for child customers (under 10) not visited in 6 months
            if ($age < Alert::CUSTOMER_CHILD_AGE && $daysSinceLastOrder >= Alert::CUSTOMER_INACTIVE_CHILD_DAYS) {
                $alertType = Alert::TYPE_CUSTOMER_INACTIVE_CHILD;
            }
            // Check for minor customers (10-16) not visited in 12 months
            elseif ($age >= Alert::CUSTOMER_CHILD_AGE && $age < Alert::CUSTOMER_MINOR_AGE && $daysSinceLastOrder >= Alert::CUSTOMER_INACTIVE_MINOR_DAYS) {
                $alertType = Alert::TYPE_CUSTOMER_INACTIVE_MINOR;
            }
            // Check for adult customers (16+) not visited in 24 months
            elseif ($age >= Alert::CUSTOMER_MINOR_AGE && $daysSinceLastOrder >= Alert::CUSTOMER_INACTIVE_ADULT_DAYS) {
                $alertType = Alert::TYPE_CUSTOMER_INACTIVE_ADULT;
            }

            if ($alertType) {
                // Check if alert already exists and is unresolved
                $existingAlert = Alert::where('customer_id', $customer->id)
                    ->where('type', $alertType)
                    ->unresolved()
                    ->first();

                if (!$existingAlert) {
                    Alert::createCustomerInactiveAlert($customer, $alertType);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Generate alerts for products with low stock
     */
    public function generateProductStockAlerts(?int $storeId = null): int
    {
        $count = 0;

        // Get all store products
        $storeProducts = StoreProducts::query()
            ->with(['product', 'store'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->get();

        foreach ($storeProducts as $storeProduct) {
            $currentStock = $storeProduct->stock;
            $minStock = $storeProduct->product->stock_min ?? 0;
            $maxStock = $storeProduct->product->stock_max ?? null;

            $alertType = null;
            $threshold = null;

            // Check for out of stock
            if ($currentStock <= 0) {
                $alertType = Alert::TYPE_PRODUCT_OUT_OF_STOCK;
            }
            // Check for low stock (if min_stock is defined and current stock is below it)
            elseif ($minStock > 0 && $currentStock <= $minStock) {
                $alertType = Alert::TYPE_PRODUCT_LOW_STOCK;
                $threshold = $minStock;
            }
            // Check for overstock (if max_stock is defined and current stock exceeds it)
            elseif ($maxStock && $currentStock > $maxStock) {
                $alertType = Alert::TYPE_PRODUCT_OVERSTOCK;
                $threshold = $maxStock;
            }

            if ($alertType) {
                // Check if alert already exists and is unresolved
                $existingAlert = Alert::where('product_id', $storeProduct->product_id)
                    ->where('store_id', $storeProduct->store_id)
                    ->where('type', $alertType)
                    ->unresolved()
                    ->first();

                if (!$existingAlert) {
                    Alert::createProductStockAlert(
                        $storeProduct->product,
                        $storeProduct->store,
                        $alertType,
                        $currentStock,
                        $threshold
                    );
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Generate alerts for specific products with low stock
     */
    public function generateProductStockAlertsForProducts(array $productIds, ?int $storeId = null): int
    {
        $count = 0;

        // Get specific store products by product IDs
        $storeProducts = StoreProducts::query()
            ->with(['product', 'store'])
            ->whereIn('product_id', $productIds)
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->get();

        foreach ($storeProducts as $storeProduct) {
            $currentStock = $storeProduct->stock;
            $minStock = $storeProduct->product->stock_min ?? 0;
            $maxStock = $storeProduct->product->stock_max ?? null;

            $alertType = null;
            $threshold = null;

            // Check for out of stock
            if ($currentStock <= 0) {
                $alertType = Alert::TYPE_PRODUCT_OUT_OF_STOCK;
            }
            // Check for low stock (if min_stock is defined and current stock is below it)
            elseif ($minStock > 0 && $currentStock <= $minStock) {
                $alertType = Alert::TYPE_PRODUCT_LOW_STOCK;
                $threshold = $minStock;
            }
            // Check for overstock (if max_stock is defined and current stock exceeds it)
            elseif ($maxStock && $currentStock > $maxStock) {
                $alertType = Alert::TYPE_PRODUCT_OVERSTOCK;
                $threshold = $maxStock;
            }

            if ($alertType) {
                // Check if alert already exists and is unresolved
                $existingAlert = Alert::where('product_id', $storeProduct->product_id)
                    ->where('store_id', $storeProduct->store_id)
                    ->where('type', $alertType)
                    ->unresolved()
                    ->first();

                if (!$existingAlert) {
                    Alert::createProductStockAlert(
                        $storeProduct->product,
                        $storeProduct->store,
                        $alertType,
                        $currentStock,
                        $threshold
                    );
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Generate all types of alerts
     */
    public function generateAllAlerts(?int $storeId = null): array
    {
        $results = [
            'customer_alerts' => $this->generateCustomerAlerts($storeId),
            'product_alerts' => $this->generateProductStockAlerts($storeId),
        ];

        $results['total'] = array_sum($results);

        return $results;
    }

    /**
     * Get alerts for a store with optional filtering
     */
    public function getAlerts(?int $storeId = null, array $filters = []): Collection
    {
        $query = Alert::query()
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->with(['customer', 'product', 'store', 'user', 'resolvedBy']);

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        if (isset($filters['is_resolved'])) {
            $query->where('is_resolved', $filters['is_resolved']);
        }

        // Order by severity (critical first) and creation date
        return $query->orderByRaw("
            CASE severity
                WHEN 'critical' THEN 1
                WHEN 'high' THEN 2
                WHEN 'medium' THEN 3
                WHEN 'low' THEN 4
                ELSE 5
            END
        ")->orderBy('created_at', 'desc')->limit(10)->get();
    }

    /**
     * Mark alert as read
     */
    public function markAsRead(int $alertId): bool
    {
        $alert = Alert::find($alertId);
        if ($alert) {
            $alert->markAsRead();
            return true;
        }
        return false;
    }

    /**
     * Mark alert as resolved
     */
    public function markAsResolved(int $alertId, ?int $userId = null): bool
    {
        $alert = Alert::find($alertId);
        if ($alert) {
            $user = $userId ? \App\Models\User::find($userId) : null;
            $alert->markAsResolved($user);
            return true;
        }
        return false;
    }

    /**
     * Get alert statistics
     */
    public function getAlertStats(?int $storeId = null): array
    {
        $query = Alert::query()->when($storeId, fn($q) => $q->where('store_id', $storeId));

        return [
            'total' => $query->count(),
            'unread' => (clone $query)->unread()->count(),
            'unresolved' => (clone $query)->unresolved()->count(),
            'critical' => (clone $query)->where('severity', Alert::SEVERITY_CRITICAL)->count(),
            'by_type' => [
                Alert::TYPE_CUSTOMER_INACTIVE_CHILD => (clone $query)->where('type', Alert::TYPE_CUSTOMER_INACTIVE_CHILD)->count(),
                Alert::TYPE_CUSTOMER_INACTIVE_MINOR => (clone $query)->where('type', Alert::TYPE_CUSTOMER_INACTIVE_MINOR)->count(),
                Alert::TYPE_CUSTOMER_INACTIVE_ADULT => (clone $query)->where('type', Alert::TYPE_CUSTOMER_INACTIVE_ADULT)->count(),
                Alert::TYPE_PRODUCT_LOW_STOCK => (clone $query)->where('type', Alert::TYPE_PRODUCT_LOW_STOCK)->count(),
                Alert::TYPE_PRODUCT_OUT_OF_STOCK => (clone $query)->where('type', Alert::TYPE_PRODUCT_OUT_OF_STOCK)->count(),
                Alert::TYPE_PRODUCT_OVERSTOCK => (clone $query)->where('type', Alert::TYPE_PRODUCT_OVERSTOCK)->count(),
            ],
            'by_category' => [
                Alert::CATEGORY_CUSTOMER => (clone $query)->where('category', Alert::CATEGORY_CUSTOMER)->count(),
                Alert::CATEGORY_PRODUCT => (clone $query)->where('category', Alert::CATEGORY_PRODUCT)->count(),
                Alert::CATEGORY_STAFF => (clone $query)->where('category', Alert::CATEGORY_STAFF)->count(),
                Alert::CATEGORY_SYSTEM => (clone $query)->where('category', Alert::CATEGORY_SYSTEM)->count(),
            ],
        ];
    }

    /**
     * Clean up old resolved alerts (optional maintenance method)
     */
    public function cleanupOldAlerts(int $daysOld = 90): int
    {
        return Alert::where('is_resolved', true)
            ->where('resolved_at', '<', now()->subDays($daysOld))
            ->delete();
    }
}
