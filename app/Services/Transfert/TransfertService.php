<?php

namespace App\Services\Transfert;

use App\Models\StoreProducts;
use App\Models\Transfert;
use App\Models\TransfertItem;
use App\Repositories\Transfert\TransfertRepository;
use App\Services\Alert\AlertService;
use Illuminate\Support\Facades\DB;

class TransfertService
{
    public function __construct(
        private readonly TransfertRepository $transfertRepository,
        private readonly AlertService $alertService
    ) {}

    /**
     * Find a transfer by ID
     */
    public function findById(int $id): ?Transfert
    {
        return Transfert::with(['sourceStore', 'targetStore', 'items.product', 'createdBy', 'receivedBy'])->find($id);
    }

    /**
     * Get all transfers for a store
     */
    public function getByStore(int $storeId, ?string $type = null)
    {
        return $this->transfertRepository->getByStore($storeId, $type);
    }

    /**
     * Create a new transfer
     */
    public function create(array $attributes): Transfert
    {
        return DB::transaction(function () use ($attributes) {
            // Validate stock availability in source store
            if (isset($attributes['items']) && is_array($attributes['items'])) {
                foreach ($attributes['items'] as $item) {
                    $storeProduct = StoreProducts::where(StoreProducts::COL_STORE_ID, $attributes['source_store_id'])
                        ->where(StoreProducts::COL_PRODUCT_ID, $item['product_id'])
                        ->first();

                    if (!$storeProduct) {
                        throw new \Exception("Product with ID {$item['product_id']} not found in source store");
                    }

                    if ($storeProduct->stock < $item['quantity']) {
                        throw new \Exception(
                            "Insufficient stock for product ID {$item['product_id']}. " .
                            "Available: {$storeProduct->stock}, Requested: {$item['quantity']}"
                        );
                    }
                }
            }

            // Generate reference
            $reference = $this->generateReference();

            // Create transfer
            $transfert = $this->transfertRepository->create([
                Transfert::COL_REFERENCE => $reference,
                Transfert::COL_SOURCE_STORE_ID => $attributes['source_store_id'],
                Transfert::COL_TARGET_STORE_ID => $attributes['target_store_id'],
                Transfert::COL_STATUS => $attributes['status'] ?? 'pending',
                Transfert::COL_CREATED_BY => auth()->id(),
                Transfert::COL_NOTE => $attributes['note'] ?? null,
                Transfert::COL_META => $attributes['meta'] ?? null,
                Transfert::COL_STORE_ID => currentStoreId(),
            ]);

            // Create transfer items
            if (isset($attributes['items']) && is_array($attributes['items'])) {
                foreach ($attributes['items'] as $item) {
                    TransfertItem::create([
                        TransfertItem::COL_TRANSFERT_ID => $transfert->id,
                        TransfertItem::COL_PRODUCT_ID => $item['product_id'],
                        TransfertItem::COL_QUANTITY => $item['quantity'],
                        TransfertItem::COL_NOTE => $item['note'] ?? null,
                    ]);
                }
            }

            return $this->findById($transfert->id);
        });
    }

    /**
     * Update transfer status
     */
    public function updateStatus(int $id, string $status, array $additionalData = []): Transfert
    {
        return DB::transaction(function () use ($id, $status, $additionalData) {
            $transfert = $this->findById($id);

            if (!$transfert) {
                throw new \Exception('Transfer not found');
            }

            $updateData = [Transfert::COL_STATUS => $status];

            if ($status === 'in_transit') {
                $updateData[Transfert::COL_SENT_AT] = now();

                // Deduct from source store for all items (only if not already deducted)
                if (!$transfert->sent_at) {
                    $productIds = [];
                    foreach ($transfert->items as $item) {
                        $this->updateStoreStock(
                            $transfert->source_store_id,
                            $item->product_id,
                            -$item->quantity
                        );
                        $productIds[] = $item->product_id;
                    }
                    // Check alerts for affected products at source store
                    if (!empty($productIds)) {
                        $this->alertService->generateProductStockAlertsForProducts($productIds, $transfert->source_store_id);
                    }
                }
            }

            if ($status === 'completed') {
                $productIds = [];
                
                // If not already sent, mark as sent and deduct from source
                if (!$transfert->sent_at) {
                    $updateData[Transfert::COL_SENT_AT] = now();

                    // Deduct from source store for all items
                    foreach ($transfert->items as $item) {
                        $this->updateStoreStock(
                            $transfert->source_store_id,
                            $item->product_id,
                            -$item->quantity
                        );
                        $productIds[] = $item->product_id;
                    }
                    
                    // Check alerts for source store
                    if (!empty($productIds)) {
                        $this->alertService->generateProductStockAlertsForProducts($productIds, $transfert->source_store_id);
                    }
                }

                $updateData[Transfert::COL_RECEIVED_AT] = now();
                $updateData[Transfert::COL_RECEIVED_BY] = auth()->id();

                // Add to target store for all items
                foreach ($transfert->items as $item) {
                    $this->updateStoreStock(
                        $transfert->target_store_id,
                        $item->product_id,
                        $item->quantity
                    );
                    if (!in_array($item->product_id, $productIds)) {
                        $productIds[] = $item->product_id;
                    }
                }
                
                // Check alerts for target store
                if (!empty($productIds)) {
                    $this->alertService->generateProductStockAlertsForProducts($productIds, $transfert->target_store_id);
                }
            }

            if (isset($additionalData['note'])) {
                $updateData[Transfert::COL_NOTE] = $additionalData['note'];
            }

            $this->transfertRepository->update($id, $updateData);

            return $this->findById($id);
        });
    }

    /**
     * Delete a transfer
     */
    public function delete(int $id): bool
    {
        $transfert = $this->findById($id);

        if (!$transfert) {
            throw new \Exception('Transfer not found');
        }

        if ($transfert->status === 'completed') {
            throw new \Exception('Cannot delete a completed transfer');
        }

        return $this->transfertRepository->delete($id);
    }

    /**
     * Generate unique reference
     */
    private function generateReference(): string
    {
        return 'TRF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    /**
     * Update store product stock
     */
    private function updateStoreStock(int $storeId, int $productId, float $quantity): void
    {
        $storeProduct = StoreProducts::where(StoreProducts::COL_STORE_ID, $storeId)
            ->where(StoreProducts::COL_PRODUCT_ID, $productId)
            ->first();

        if ($storeProduct) {
            $storeProduct->increment(StoreProducts::COL_STOCK, $quantity);
        }
    }
}
