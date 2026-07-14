<?php

namespace App\Services\Purchase;

use App\Enums\EnumOrderStatue;
use App\Models\OrderPurchase;
use App\Models\OrderPurchaseItems;
use App\Models\PurchaseDelivery;
use App\Models\PurchaseDeliveryItem;
use App\Models\StockMovement;
use App\Repositories\Purchase\PurchaseDeliveryRepository;
use App\Repositories\Purchase\PurchaseRepository;
use App\Services\Setting\SettingService;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseDeliveryService
{
    public function __construct(
        private readonly PurchaseDeliveryRepository $deliveryRepository,
        private readonly PurchaseRepository $purchaseRepository,
        private readonly SettingService $settingService,
        private readonly StockService $stockService
    ) {}

    /**
     * Find delivery by ID
     */
    public function findById(int $id): ?PurchaseDelivery
    {
        return $this->deliveryRepository->find($id);
    }

    /**
     * Find delivery with relationships
     */
    public function findWithRelations(int $id, array $relations): ?PurchaseDelivery
    {
        return $this->deliveryRepository->findWith($id, PurchaseDelivery::COL_ID, $relations);
    }

    /**
     * Get all deliveries for a purchase order
     */
    public function getDeliveriesForPurchaseOrder(int $purchaseOrderId)
    {
        return PurchaseDelivery::where(PurchaseDelivery::COL_ORDER_PURCHASE_ID, $purchaseOrderId)
            ->with(['deliveryItems.product', 'receivedBy', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Create a new delivery note (Bon de Livraison)
     * 
     * @param array $data
     * @return PurchaseDelivery
     * @throws \Exception
     */
    public function createDelivery(array $data): PurchaseDelivery
    {
        return DB::transaction(function () use ($data) {
            // Find the purchase order
            $purchase = $this->purchaseRepository->find($data['order_purchase_id']);

            if (!$purchase) {
                throw new \Exception('Purchase order not found');
            }

            // Check if purchase order is in appropriate status
            if (!in_array($purchase->status, [
                EnumOrderStatue::ORDERED->value,
                EnumOrderStatue::RECEIVING->value,
                EnumOrderStatue::PARTIALLY_RECEIVED->value,
                EnumOrderStatue::PENDING->value
            ])) {
                throw new \Exception('Purchase order must be ordered before receiving deliveries');
            }

            // Generate delivery number
            $deliveryNumber = $this->generateDeliveryNumber();

            // Create the delivery
            $delivery = $this->deliveryRepository->create([
                PurchaseDelivery::COL_DELIVERY_NUMBER => $deliveryNumber,
                PurchaseDelivery::COL_ORDER_PURCHASE_ID => $data['order_purchase_id'],
                PurchaseDelivery::COL_STORE_ID => $purchase->store_id,
                PurchaseDelivery::COL_SUPPLIER_ID => $purchase->supplier_id,
                PurchaseDelivery::COL_RECEIVED_BY => $data['received_by'] ?? auth()->id(),
                PurchaseDelivery::COL_DELIVERY_DATE => $data['delivery_date'] ?? now()->format('Y-m-d'),
                PurchaseDelivery::COL_SUPPLIER_DELIVERY_NOTE => $data['supplier_delivery_note'] ?? null,
                PurchaseDelivery::COL_TRANSPORT_COMPANY => $data['transport_company'] ?? null,
                PurchaseDelivery::COL_DRIVER_NAME => $data['driver_name'] ?? null,
                PurchaseDelivery::COL_VEHICLE_PLATE => $data['vehicle_plate'] ?? null,
                PurchaseDelivery::COL_DELIVERY_NOTE => $data['delivery_note'] ?? null,
                PurchaseDelivery::COL_QUALITY_CHECK_NOTE => $data['quality_check_note'] ?? null,
                PurchaseDelivery::COL_HAS_ISSUES => $data['has_issues'] ?? false,
                PurchaseDelivery::COL_STATUS => PurchaseDelivery::STATUS_DRAFT,
            ]);

            // Create delivery items
            foreach ($data['items'] as $itemData) {
                // Find the purchase order item
                $purchaseItem = OrderPurchaseItems::find($itemData['order_purchase_item_id']);

                if (!$purchaseItem) {
                    throw new \Exception('Purchase order item not found');
                }

                // Calculate remaining quantity
                $remainingQty = $purchaseItem->quantity - $purchaseItem->received_quantity;

                if ($itemData['delivered_quantity'] > $remainingQty) {
                    throw new \Exception("Delivered quantity exceeds remaining quantity for product: {$purchaseItem->name}");
                }

                // Create delivery item
                $delivery->deliveryItems()->create([
                    PurchaseDeliveryItem::COL_ORDER_PURCHASE_ITEM_ID => $itemData['order_purchase_item_id'],
                    PurchaseDeliveryItem::COL_PRODUCT_ID => $purchaseItem->product_id,
                    PurchaseDeliveryItem::COL_ORDERED_QUANTITY => $purchaseItem->quantity,
                    PurchaseDeliveryItem::COL_DELIVERED_QUANTITY => $itemData['delivered_quantity'],
                    PurchaseDeliveryItem::COL_ACCEPTED_QUANTITY => $itemData['accepted_quantity'] ?? $itemData['delivered_quantity'],
                    PurchaseDeliveryItem::COL_REJECTED_QUANTITY => $itemData['rejected_quantity'] ?? 0,
                    PurchaseDeliveryItem::COL_UNIT_PRICE => $purchaseItem->price,
                    PurchaseDeliveryItem::COL_TOTAL_PRICE => ($itemData['accepted_quantity'] ?? $itemData['delivered_quantity']) * $purchaseItem->price,
                    PurchaseDeliveryItem::COL_REJECTION_REASON => $itemData['rejection_reason'] ?? null,
                    PurchaseDeliveryItem::COL_BATCH_NUMBER => $itemData['batch_number'] ?? null,
                    PurchaseDeliveryItem::COL_EXPIRY_DATE => $itemData['expiry_date'] ?? null,
                ]);
            }

            // Refresh to get relationships
            return $delivery->fresh(['deliveryItems.product', 'orderPurchase', 'receivedBy']);
        });
    }

    /**
     * Validate delivery and update stock
     * 
     * @param int $deliveryId
     * @return PurchaseDelivery
     * @throws \Exception
     */
    public function validateDelivery(int $deliveryId): PurchaseDelivery
    {
        $delivery = DB::transaction(function () use ($deliveryId) {
            $delivery = $this->deliveryRepository->find($deliveryId);

            if (!$delivery) {
                throw new \Exception('Delivery not found');
            }

            if ($delivery->status !== PurchaseDelivery::STATUS_DRAFT) {
                throw new \Exception('Only draft deliveries can be validated');
            }

            $productIds = [];
            
            // Update stock for each delivery item
            foreach ($delivery->deliveryItems as $item) {
                // Track product IDs for batch alert checking
                $productIds[] = $item->product_id;
                
                // Update stock using StockService (skip per-item alerts, batch them after)
                $this->stockService->processStoreProductMovement([
                    StockMovement::COL_STORE_ID => $delivery->store_id,
                    StockMovement::COL_PRODUCT_ID => $item->product_id,
                    StockMovement::COL_QUANTITY => $item->accepted_quantity,
                    StockMovement::COL_TYPE => StockMovement::TYPE_PURCHASE,
                    StockMovement::COL_DIRECTION => StockMovement::DIRECTION_IN,
                    StockMovement::COL_UNIT_COST => $item->unit_price,
                    StockMovement::COL_TOTAL_COST => $item->total_price,
                    StockMovement::COL_REFERENCEABLE_TYPE => PurchaseDelivery::class,
                    StockMovement::COL_REFERENCEABLE_ID => $delivery->id,
                    StockMovement::COL_USER_ID => auth()->id(),
                    StockMovement::COL_NOTE => 'Delivery validated: ' . $delivery->delivery_number,
                ], skipAlerts: true);

                // Update purchase item received quantity
                $purchaseItem = $item->orderPurchaseItem;
                $purchaseItem->update([
                    OrderPurchaseItems::COL_RECEIVED_QUANTITY => $purchaseItem->received_quantity + $item->accepted_quantity,
                    OrderPurchaseItems::COL_REMAINING_QUANTITY => $purchaseItem->quantity - ($purchaseItem->received_quantity + $item->accepted_quantity),
                ]);
            }

            // Update delivery status
            $delivery->update([
                PurchaseDelivery::COL_STATUS => PurchaseDelivery::STATUS_VALIDATED,
            ]);

            // Check and update purchase order completeness
            $this->checkOrderCompleteness($delivery->order_purchase_id);
            
            // Store product IDs for alert checking outside transaction
            $delivery->_productIds = array_unique($productIds);

            return $delivery->fresh(['deliveryItems.product', 'orderPurchase']);
        });
        
        // Batch check alerts for all affected products AFTER transaction completes
        // This prevents alert generation from slowing down the critical stock update transaction
        if (!empty($delivery->_productIds)) {
            try {
                $this->stockService->checkStockAlertsForProducts($delivery->store_id, $delivery->_productIds);
            } catch (\Exception $e) {
                // Log but don't fail - alerts are not critical
                Log::warning('Failed to check stock alerts after delivery validation', [
                    'delivery_id' => $delivery->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return $delivery;
    }

    /**
     * Cancel a delivery
     * 
     * @param int $deliveryId
     * @return PurchaseDelivery
     * @throws \Exception
     */
    public function cancelDelivery(int $deliveryId): PurchaseDelivery
    {
        return DB::transaction(function () use ($deliveryId) {
            $delivery = $this->deliveryRepository->find($deliveryId);

            if (!$delivery) {
                throw new \Exception('Delivery not found');
            }

            if ($delivery->status === PurchaseDelivery::STATUS_VALIDATED) {
                throw new \Exception('Cannot cancel a validated delivery. Please create an adjustment instead.');
            }

            // Update delivery status
            $delivery->update([
                PurchaseDelivery::COL_STATUS => PurchaseDelivery::STATUS_CANCELLED,
            ]);

            return $delivery->fresh();
        });
    }

    /**
     * Check if purchase order is fully received and update status
     * 
     * @param int $orderId
     * @return void
     */
    public function checkOrderCompleteness(int $orderId): void
    {
        $purchase = $this->purchaseRepository->find($orderId);

        if (!$purchase) {
            return;
        }

        // Count items
        $totalItems = $purchase->orderItems->count();
        $fullyReceivedItems = $purchase->orderItems->filter(function ($item) {
            return $item->received_quantity >= $item->quantity;
        })->count();

        $partiallyReceivedItems = $purchase->orderItems->filter(function ($item) {
            return $item->received_quantity > 0 && $item->received_quantity < $item->quantity;
        })->count();

        // Determine status
        $deliveryStatus = 'not_started';
        $status = $purchase->status;

        if ($fullyReceivedItems === $totalItems) {
            // All items fully received
            $deliveryStatus = 'fully_received';
            $status = EnumOrderStatue::FULLY_RECEIVED->value;

            // Update last delivery date
            $purchase->update([
                OrderPurchase::COL_DELIVERY_STATUS => $deliveryStatus,
                OrderPurchase::COL_STATUS => $status,
                OrderPurchase::COL_LAST_DELIVERY_DATE => now()->format('Y-m-d'),
            ]);
        } elseif ($partiallyReceivedItems > 0 || $fullyReceivedItems > 0) {
            // Some items received
            $deliveryStatus = 'partially_received';
            $status = EnumOrderStatue::PARTIALLY_RECEIVED->value;

            // Update first delivery date if not set
            $updateData = [
                OrderPurchase::COL_DELIVERY_STATUS => $deliveryStatus,
                OrderPurchase::COL_STATUS => $status,
            ];

            if (!$purchase->first_delivery_date) {
                $updateData[OrderPurchase::COL_FIRST_DELIVERY_DATE] = now()->format('Y-m-d');
            }

            $purchase->update($updateData);
        }
    }

    /**
     * Generate delivery number (BL-0001)
     * 
     * @return string
     */
    private function generateDeliveryNumber(): string
    {
        $currentNumber = (int) $this->settingService->getValue('delivery_number', 0);
        $nextNumber = $currentNumber + 1;
        $prefix = $this->settingService->getValue('prefix_delivery', 'BL-');

        // Increment the sequence
        $this->settingService->setValue('delivery_number', $nextNumber, 'integer', 'Current delivery note sequence number');

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
