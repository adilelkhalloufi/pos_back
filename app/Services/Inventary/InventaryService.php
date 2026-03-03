<?php

namespace App\Services\Inventary;

use App\Models\Inventary;
use App\Models\InventaryItem;
use App\Models\StoreProducts;
use App\Repositories\Inventary\InventaryRepository;
use App\Services\Alert\AlertService;
use Illuminate\Support\Facades\DB;

class InventaryService
{
    public function __construct(
        private readonly InventaryRepository $inventaryRepository,
        private readonly AlertService $alertService
    ) {}

    /**
     * Find an inventory by ID
     */
    public function findById(int $id): ?Inventary
    {
        return $this->inventaryRepository->findWithItems($id);
    }

    /**
     * Get all inventories for a store
     */
    public function getByStore(int $storeId)
    {
        return $this->inventaryRepository->getByStore($storeId);
    }

    /**
     * Create a new inventory
     */
    public function create(array $attributes): Inventary
    {
        return DB::transaction(function () use ($attributes) {
            // Generate reference
            $reference = $this->generateReference();

            // Create inventory
            $inventary = $this->inventaryRepository->create([
                Inventary::COL_REFERENCE => $reference,
                Inventary::COL_STORE_ID => currentStoreId(),
                Inventary::COL_STORE_ID => $attributes['store_id'],
                Inventary::COL_STATUS => 'pending',
                Inventary::COL_CREATED_BY => auth()->id(),
                Inventary::COL_NOTE => $attributes['note'] ?? null,
                Inventary::COL_META => $attributes['meta'] ?? null,
            ]);

            // Get all products in store
            $storeProducts = StoreProducts::where(StoreProducts::COL_STORE_ID, $attributes['store_id'])
                ->get();

            // Create inventory items
            foreach ($storeProducts as $storeProduct) {
                InventaryItem::create([
                    InventaryItem::COL_INVENTARY_ID => $inventary->id,
                    InventaryItem::COL_PRODUCT_ID => $storeProduct->product_id,
                    InventaryItem::COL_EXPECTED_QUANTITY => $storeProduct->stock,
                    InventaryItem::COL_STATUS => 'pending',
                ]);
            }

            // Update total items count
            $inventary->update([
                Inventary::COL_TOTAL_ITEMS => $storeProducts->count()
            ]);

            return $this->inventaryRepository->findWithItems($inventary->id);
        });
    }

    /**
     * Start inventory
     */
    public function start(int $id): Inventary
    {
        $inventary = $this->inventaryRepository->find($id);

        if (!$inventary) {
            throw new \Exception('Inventory not found');
        }

        if ($inventary->status !== 'pending') {
            throw new \Exception('Inventory must be in pending status to start');
        }

        $this->inventaryRepository->update($id, [
            Inventary::COL_STATUS => 'in_progress',
            Inventary::COL_STARTED_AT => now(),
        ]);

        return $this->inventaryRepository->findWithItems($id);
    }

    /**
     * Update inventory item
     */
    public function updateItem(int $inventaryId, int $itemId, array $attributes): InventaryItem
    {
        $item = InventaryItem::where('id', $itemId)
            ->where('inventary_id', $inventaryId)
            ->first();

        if (!$item) {
            throw new \Exception('Inventory item not found');
        }

        $actualQuantity = $attributes['actual_quantity'];
        $difference = $actualQuantity - $item->expected_quantity;

        $item->update([
            InventaryItem::COL_ACTUAL_QUANTITY => $actualQuantity,
            InventaryItem::COL_DIFFERENCE => $difference,
            InventaryItem::COL_STATUS => $difference != 0 ? 'discrepancy' : 'checked',
            InventaryItem::COL_NOTE => $attributes['note'] ?? null,
        ]);

        // Update inventory checked items count
        $inventary = Inventary::find($inventaryId);
        $checkedCount = InventaryItem::where('inventary_id', $inventaryId)
            ->whereNotNull('actual_quantity')
            ->count();

        $totalDifference = InventaryItem::where('inventary_id', $inventaryId)
            ->sum('difference');

        $inventary->update([
            Inventary::COL_CHECKED_ITEMS => $checkedCount,
            Inventary::COL_TOTAL_DIFFERENCE => $totalDifference,
        ]);

        return $item;
    }

    /**
     * Complete inventory
     */
    public function complete(int $id, bool $applyAdjustments = true): Inventary
    {
        return DB::transaction(function () use ($id, $applyAdjustments) {
            $inventary = $this->inventaryRepository->findWithItems($id);

            if (!$inventary) {
                throw new \Exception('Inventory not found');
            }

            if ($inventary->status !== 'in_progress') {
                throw new \Exception('Inventory must be in progress to complete');
            }

            // Check if all items are checked
            $uncheckedItems = $inventary->items()->whereNull('actual_quantity')->count();
            if ($uncheckedItems > 0) {
                throw new \Exception('All items must be checked before completing inventory');
            }

            // Apply adjustments if requested
            if ($applyAdjustments) {
                foreach ($inventary->items as $item) {
                    if ($item->difference != 0) {
                        $storeProduct = StoreProducts::where(StoreProducts::COL_STORE_ID, $inventary->store_id)
                            ->where(StoreProducts::COL_PRODUCT_ID, $item->product_id)
                            ->first();

                        if ($storeProduct) {
                            $storeProduct->update([
                                StoreProducts::COL_STOCK => $item->actual_quantity
                            ]);
                        }
                    }
                }

                // Check for stock alerts after inventory adjustments
                $this->alertService->generateProductStockAlerts($inventary->store_id);
            }

            // Update inventory status
            $this->inventaryRepository->update($id, [
                Inventary::COL_STATUS => 'completed',
                Inventary::COL_COMPLETED_AT => now(),
                Inventary::COL_COMPLETED_BY => auth()->id(),
            ]);

            return $this->inventaryRepository->findWithItems($id);
        });
    }

    /**
     * Cancel inventory
     */
    public function cancel(int $id): Inventary
    {
        $inventary = $this->inventaryRepository->find($id);

        if (!$inventary) {
            throw new \Exception('Inventory not found');
        }

        if ($inventary->status === 'completed') {
            throw new \Exception('Cannot cancel a completed inventory');
        }

        $this->inventaryRepository->update($id, [
            Inventary::COL_STATUS => 'cancelled',
        ]);

        return $this->inventaryRepository->find($id);
    }



    /**
     * Generate unique reference
     */
    private function generateReference(): string
    {
        return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
