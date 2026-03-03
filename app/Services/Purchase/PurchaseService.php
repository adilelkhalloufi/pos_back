<?php

namespace App\Services\Purchase;

use App\Enums\EnumOrderStatue;
use App\Models\OrderPurchase;
use App\Models\StockMovement;
use App\Repositories\Purchase\PurchaseRepository;
use App\Services\Setting\SettingService;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        private readonly PurchaseRepository $purchaseRepository,
        private readonly SettingService $settingService,
        private readonly StockService $stockService
    ) {}


    public function findById(int $id)
    {
        return $this->purchaseRepository->find($id);
    }

    public function findwithRelations(int $id, array $relations)
    {
        return $this->purchaseRepository->findWith($id, OrderPurchase::COL_ID, $relations);
    }

    public function create(array $attributes): ?OrderPurchase
    {
        $purchase = $this->purchaseRepository->create([
            OrderPurchase::COL_ORDER_NUMBER => 'Brouillon',
            OrderPurchase::COL_SUPPLIER_ID => $attributes[OrderPurchase::COL_SUPPLIER_ID],
            OrderPurchase::COL_REFERENCE => $attributes[OrderPurchase::COL_REFERENCE],
            OrderPurchase::COL_PAID_METHOD_ID => $attributes[OrderPurchase::COL_PAID_METHOD_ID],
            OrderPurchase::COL_PUBLIC_NOTE => $attributes[OrderPurchase::COL_PUBLIC_NOTE],
            OrderPurchase::COL_PRIVATE_NOTE => $attributes[OrderPurchase::COL_PRIVATE_NOTE],
            OrderPurchase::COL_STATUS => EnumOrderStatue::PENDING->value,
            OrderPurchase::COL_USER_ID => auth()->id(),
            OrderPurchase::COL_STORE_ID => currentStoreId()
        ]);

        $purchase->orderItems()->createMany($attributes['details']);


        return $purchase;
    }

    /**
     * Activate/Approve purchase order by admin
     * - Updates status to COMPLETED
     * - Generates order number from settings (prefix + sequence)
     * - Updates StoreProducts stock quantities
     * - Creates stock movement records
     */
    public function approvePurchase(int $purchaseId): OrderPurchase
    {
        return DB::transaction(function () use ($purchaseId) {
            // Find the purchase order
            $purchase = $this->purchaseRepository->find($purchaseId);

            if (!$purchase) {
                throw new \Exception('Purchase order not found');
            }



            $orderNumber = $this->settingService->getNextPurchaseOrderNumber();

            // Update purchase order status and order number
            $purchase->update([
                OrderPurchase::COL_ORDER_NUMBER => $orderNumber,
                OrderPurchase::COL_STATUS => EnumOrderStatue::COMPLETED->value,
            ]);

            // Increment the sequence in settings
            $this->settingService->incrementPurchaseOrderNumber();

            // Process each purchase item
            foreach ($purchase->orderItems as $item) {
                // Use StockService to handle stock movement
                $this->stockService->processStoreProductMovement([
                    StockMovement::COL_STORE_ID => $purchase->{OrderPurchase::COL_STORE_ID},
                    StockMovement::COL_PRODUCT_ID => $item->product_id,
                    StockMovement::COL_QUANTITY => $item->quantity,
                    StockMovement::COL_TYPE => StockMovement::TYPE_PURCHASE,
                    StockMovement::COL_DIRECTION => StockMovement::DIRECTION_IN,
                    StockMovement::COL_UNIT_COST => $item->price,
                    StockMovement::COL_TOTAL_COST => $item->price * $item->quantity,
                    StockMovement::COL_REFERENCEABLE_TYPE => OrderPurchase::class,
                    StockMovement::COL_REFERENCEABLE_ID => $purchase->id,
                    StockMovement::COL_USER_ID => auth()->id(),
                    StockMovement::COL_NOTE => 'Purchase order approved: ' . $orderNumber,
                ]);
            }

            // Refresh to get updated relationships
            return $purchase->fresh();
        });
    }

    public function cancelPurchase(int $purchaseId): OrderPurchase
    {
        $purchase = $this->purchaseRepository->find($purchaseId);

        if (!$purchase) {
            throw new \Exception('Purchase order not found');
        }

        // Update purchase order status to CANCELLED
        $purchase->update([
            OrderPurchase::COL_STATUS => EnumOrderStatue::CANCELLED->value,
        ]);

        return $purchase;
    }
}
