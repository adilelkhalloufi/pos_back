<?php

namespace App\Services\Stock;
use App\Models\StockMovement;
use App\Models\StoreProducts;
use App\Services\Alert\AlertService;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function __construct(
        private AlertService $alertService
    ) {}

    /**
     * Get stock movements with filters
     */
    public function getStockMovements(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = StockMovement::with(['product', 'sourceStore', 'targetStore', 'store', 'user']);

        $query = $query->where(StockMovement::COL_STORE_ID, currentStoreId());
        // Apply filters
        if (!empty($filters['product_id'])) {
            $query->where(StockMovement::COL_PRODUCT_ID, $filters['product_id']);
        }

        if (!empty($filters['source_store_id'])) {
            $query->where(StockMovement::COL_SOURCE_STORE_ID, $filters['source_store_id']);
        }

        if (!empty($filters['target_store_id'])) {
            $query->where(StockMovement::COL_TARGET_STORE_ID, $filters['target_store_id']);
        }

        if (!empty($filters['store_id'])) {
            $query->where(StockMovement::COL_STORE_ID, $filters['store_id']);
        }

        if (!empty($filters['type'])) {
            $query->where(StockMovement::COL_TYPE, $filters['type']);
        }

        if (!empty($filters['direction'])) {
            $query->where(StockMovement::COL_DIRECTION, $filters['direction']);
        }

        if (!empty($filters['user_id'])) {
            $query->where(StockMovement::COL_USER_ID, $filters['user_id']);
        }

        if (!empty($filters['referenceable_type'])) {
            $query->where(StockMovement::COL_REFERENCEABLE_TYPE, $filters['referenceable_type']);
        }

        if (!empty($filters['referenceable_id'])) {
            $query->where(StockMovement::COL_REFERENCEABLE_ID, $filters['referenceable_id']);
        }

        // Date filters
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['created_at_from'])) {
            $query->where('created_at', '>=', $filters['created_at_from']);
        }

        if (!empty($filters['created_at_to'])) {
            $query->where('created_at', '<=', $filters['created_at_to']);
        }

        // Quantity filters
        if (!empty($filters['quantity_min'])) {
            $query->where(StockMovement::COL_QUANTITY, '>=', $filters['quantity_min']);
        }

        if (!empty($filters['quantity_max'])) {
            $query->where(StockMovement::COL_QUANTITY, '<=', $filters['quantity_max']);
        }

        // Cost filters
        if (!empty($filters['unit_cost_min'])) {
            $query->where(StockMovement::COL_UNIT_COST, '>=', $filters['unit_cost_min']);
        }

        if (!empty($filters['unit_cost_max'])) {
            $query->where(StockMovement::COL_UNIT_COST, '<=', $filters['unit_cost_max']);
        }

        if (!empty($filters['total_cost_min'])) {
            $query->where(StockMovement::COL_TOTAL_COST, '>=', $filters['total_cost_min']);
        }

        if (!empty($filters['total_cost_max'])) {
            $query->where(StockMovement::COL_TOTAL_COST, '<=', $filters['total_cost_max']);
        }

        // Search
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%");
                })
                    ->orWhere(StockMovement::COL_TYPE, 'like', "%{$search}%")
                    ->orWhere(StockMovement::COL_DIRECTION, 'like', "%{$search}%")
                    ->orWhere(StockMovement::COL_NOTE, 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $perPage = $filters['per_page'] ?? 200;

        return $query->paginate($perPage);
    }

    public function processStoreProductMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $storeId = $data['store_id'];
            $productId = $data['product_id'];
            $quantity = $data['quantity'];
            $type = $data['type']; // 'sale', 'purchase', 'adjustment', 'transfer'

            // Find or create StoreProduct record
            $storeProduct = StoreProducts::firstOrCreate(
                [
                    StoreProducts::COL_STORE_ID => $storeId,
                    StoreProducts::COL_PRODUCT_ID => $productId,
                ],
                [
                    StoreProducts::COL_STOCK => 0,
                    StoreProducts::COL_PRICE => $data['price'] ?? 0,
                    StoreProducts::COL_COST => $data['cost'] ?? 0,
                ]
            );

            // Get previous stock quantity
            $previousStock = $storeProduct->{StoreProducts::COL_STOCK};

            // Calculate new stock based on movement type
            $newStock = match ($type) {
                'sale', 'exit' => $previousStock - $quantity, // Subtract for outgoing
                'purchase', 'entry' => $previousStock + $quantity, // Add for incoming
                'adjustment' => $quantity, // Set to specific value
                default => $previousStock,
            };

            // Update stock quantity
            $storeProduct->update([
                StoreProducts::COL_STOCK => $newStock,
                StoreProducts::COL_COST => $data['cost'] ?? $storeProduct->{StoreProducts::COL_COST},
            ]);

            // Check for stock alerts after stock change
            $this->checkStockAlertsAfterMovement($storeId, $productId);

            // Create stock movement record
            return StockMovement::create([
                StockMovement::COL_PRODUCT_ID => $productId,
                StockMovement::COL_SOURCE_STORE_ID => $storeId,
                StockMovement::COL_TARGET_STORE_ID => $data['target_store_id'] ?? null,
                StockMovement::COL_STORE_ID => $data['store_id'] ?? $storeId,
                StockMovement::COL_TYPE => $type,
                StockMovement::COL_DIRECTION => $data['direction'] ?? ($type === 'sale' || $type === 'exit' ? 'out' : 'in'),
                StockMovement::COL_QUANTITY => $quantity,
                StockMovement::COL_UNIT_COST => $data['unit_cost'] ?? $data['price'] ?? 0,
                StockMovement::COL_TOTAL_COST => ($data['unit_cost'] ?? $data['price'] ?? 0) * $quantity,
                StockMovement::COL_PREVIOUS_STOCK => $previousStock,
                StockMovement::COL_NEW_STOCK => $newStock,
                StockMovement::COL_REFERENCEABLE_TYPE => $data['referenceable_type'] ?? null,
                StockMovement::COL_REFERENCEABLE_ID => $data['referenceable_id'] ?? null,
                StockMovement::COL_USER_ID => $data['user_id'] ?? auth()->id(),
                StockMovement::COL_NOTE => $data['note'] ?? null,
                StockMovement::COL_META => $data['meta'] ?? null,
            ]);
        });
    }

    /**
     * Check for stock alerts after a stock movement
     */
    private function checkStockAlertsAfterMovement(int $storeId, int $productId): void
    {
        // Generate product stock alerts for this specific store
        // This will check all products in the store, but it's efficient enough
        // since it's only called after actual stock changes
        $this->alertService->generateProductStockAlerts($storeId);
    }
}
