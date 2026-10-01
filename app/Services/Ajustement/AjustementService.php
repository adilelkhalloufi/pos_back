<?php

namespace App\Services\Ajustement;

use App\Models\Ajustement;
use App\Models\AjustementItem;
use App\Models\StockMovement;
use App\Models\StoreProducts;
use App\Repositories\Ajustement\AjustementRepository;
use App\Services\Alert\AlertService;
use App\Services\Stock\StockService;
use Illuminate\Support\Facades\DB;

class AjustementService
{
    public function __construct(
        private readonly AjustementRepository $ajustementRepository,
        private readonly AlertService $alertService,
        private readonly StockService $stockService
    ) {}

    /**
     * Find an adjustment by ID
     */
    public function findById(int $id): ?Ajustement
    {
        // return Ajustement::with(['store', 'items.product', 'user'])->find($id);

        return $this->ajustementRepository->getQueryBuilder()
            ->with(['store', 'items.product', 'user', 'targetStore'])
            ->find($id);
    }

    /**
     * Get all adjustments for a store
     */
    public function getByStore(int $storeId)
    {
        return $this->ajustementRepository->getByStore($storeId);
    }

    /**
     * Create a new adjustment
     */
    public function create(array $attributes): Ajustement
    {
        return DB::transaction(function () use ($attributes) {
            // Generate reference
            $reference = $this->generateReference();

            // Create adjustment record
            $ajustement = $this->ajustementRepository->create([
                Ajustement::COL_REFERENCE => $reference,
                Ajustement::COL_STORE_ID => currentStoreId(),
                Ajustement::COL_REASON => $attributes['reason'] ?? 'other',
                Ajustement::COL_NOTE => $attributes['note'] ?? null,
                Ajustement::COL_USER_ID => auth()->id(),
                Ajustement::COL_META => $attributes['meta'] ?? null,
                Ajustement::COL_STATUS => 'pending',
                Ajustement::COL_TARGET_STORE_ID => $attributes['store_id'],
            ]);

            // Create adjustment items
            if (isset($attributes['items']) && is_array($attributes['items'])) {
                foreach ($attributes['items'] as $item) {
                    // Get current stock
                    $storeProduct = StoreProducts::where(StoreProducts::COL_STORE_ID, $attributes['store_id'])
                        ->where(StoreProducts::COL_PRODUCT_ID, $item['product_id'])
                        ->first();

                    $previousStock = $storeProduct ? $storeProduct->stock : 0;

                    // Calculate new stock
                    $quantity = $item['quantity'];
                    $type = $item['type'] ?? 'increase';
                    $adjustmentQuantity = $type === 'increase' ? $quantity : -$quantity;
                    $newStock = $previousStock + $adjustmentQuantity;

                    // Create item
                    AjustementItem::create([
                        AjustementItem::COL_AJUSTEMENT_ID => $ajustement->id,
                        AjustementItem::COL_PRODUCT_ID => $item['product_id'],
                        AjustementItem::COL_TYPE => $type,
                        AjustementItem::COL_QUANTITY => $quantity,
                        AjustementItem::COL_PREVIOUS_STOCK => $previousStock,
                        AjustementItem::COL_NEW_STOCK => $newStock,
                        AjustementItem::COL_NOTE => $item['note'] ?? null,
                    ]);
                }
            }

            return $this->findById($ajustement->id);
        });
    }

    private function generateReference(): string
    {
        return 'ADJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function updateStatus(int $ajustementId, string $status): ?Ajustement
    {
        return DB::transaction(function () use ($ajustementId, $status) {
            $ajustement = $this->findById($ajustementId);

            if (!$ajustement) {
                throw new \Exception('Adjustment not found');
            }

            $updateData = [Ajustement::COL_STATUS => $status];

            // Apply stock changes when status changes to 'completed'
            if ($status === 'completed' && $ajustement->status !== 'completed') {
                foreach ($ajustement->items as $item) {
                    // Get current stock
                    $storeProduct = StoreProducts::where(StoreProducts::COL_STORE_ID, $ajustement->target_store_id)
                        ->where(StoreProducts::COL_PRODUCT_ID, $item->product_id)
                        ->first();

                    if ($storeProduct) {
                        // Calculate adjustment quantity
                        $adjustmentQuantity = $item->type === 'increase' ? $item->quantity : -$item->quantity;
                        $previousStock = (float) $storeProduct->stock;

                        // Update stock
                        $storeProduct->increment(StoreProducts::COL_STOCK, $adjustmentQuantity);

                        $this->stockService->createMovementSafely([
                            StockMovement::COL_PRODUCT_ID => $item->product_id,
                            StockMovement::COL_STORE_ID => $ajustement->target_store_id,
                            StockMovement::COL_SOURCE_STORE_ID => $ajustement->target_store_id,
                            StockMovement::COL_TYPE => StockMovement::TYPE_ADJUSTMENT,
                            StockMovement::COL_DIRECTION => $adjustmentQuantity >= 0
                                ? StockMovement::DIRECTION_IN
                                : StockMovement::DIRECTION_OUT,
                            StockMovement::COL_QUANTITY => abs((float) $item->quantity),
                            StockMovement::COL_PREVIOUS_STOCK => $previousStock,
                            StockMovement::COL_NEW_STOCK => $previousStock + (float) $adjustmentQuantity,
                            StockMovement::COL_USER_ID => auth()->id(),
                            StockMovement::COL_REFERENCEABLE_TYPE => Ajustement::class,
                            StockMovement::COL_REFERENCEABLE_ID => $ajustement->id,
                            StockMovement::COL_NOTE => 'Adjustment completed: ' . $ajustement->reference,
                        ]);
                    }
                }

                // Check for stock alerts only for adjusted products
                $productIds = $ajustement->items->pluck('product_id')->toArray();
                if (!empty($productIds)) {
                    $this->alertService->generateProductStockAlertsForProducts($productIds, $ajustement->target_store_id);
                }
            }

            $this->ajustementRepository->update($ajustementId, $updateData);

            return $this->findById($ajustementId);
        });
    }
}
