<?php

namespace App\Repositories\StoreProduct;

use App\Models\StoreProducts;
use App\Repositories\BaseRepository;

class StoreProductRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return StoreProducts::class;
    }

    /**
     * Get all store products for a specific store
     * 
     * @param int $storeId
     * @param array $relations
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStore(int $storeId, array $relations = [])
    {
        $query = $this->getQueryBuilder()
            ->where(StoreProducts::COL_STORE_ID, $storeId);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Find store product by store and product ID
     * 
     * @param int $storeId
     * @param int $productId
     * @return StoreProducts|null
     */
    public function findByStoreAndProduct(int $storeId, int $productId)
    {
        return $this->getQueryBuilder()
            ->where(StoreProducts::COL_STORE_ID, $storeId)
            ->where(StoreProducts::COL_PRODUCT_ID, $productId)
            ->first();
    }

    /**
     * Get store products with stock greater than zero
     * 
     * @param int $storeId
     * @param string|null $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInStockProducts(int $storeId, ?string $search = null)
    {
        $query = $this->getQueryBuilder()
            ->with(['product.category', 'product.unit', 'store'])
            ->where(StoreProducts::COL_STORE_ID, $storeId)
            ->where(StoreProducts::COL_STOCK, '>', 0);

        if ($search) {
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('codebar', 'LIKE', "%{$search}%")
                  ->orWhere('reference', 'LIKE', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Update stock quantity
     * 
     * @param int $storeId
     * @param int $productId
     * @param float $quantity
     * @param string $operation (add|subtract|set)
     * @return bool
     */
    public function updateStock(int $storeId, int $productId, float $quantity, string $operation = 'set'): bool
    {
        $storeProduct = $this->findByStoreAndProduct($storeId, $productId);

        if (!$storeProduct) {
            return false;
        }

        $newStock = match($operation) {
            'add' => $storeProduct->{StoreProducts::COL_STOCK} + $quantity,
            'subtract' => $storeProduct->{StoreProducts::COL_STOCK} - $quantity,
            default => $quantity,
        };

        return $this->getQueryBuilder()
            ->where(StoreProducts::COL_STORE_ID, $storeId)
            ->where(StoreProducts::COL_PRODUCT_ID, $productId)
            ->update([StoreProducts::COL_STOCK => max(0, $newStock)]) > 0;
    }

    /**
     * Get low stock products for a store
     * 
     * @param int $storeId
     * @param float $threshold
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowStockProducts(int $storeId, float $threshold = 10)
    {
        return $this->getQueryBuilder()
            ->with(['product', 'store'])
            ->where(StoreProducts::COL_STORE_ID, $storeId)
            ->where(StoreProducts::COL_STOCK, '<=', $threshold)
            ->where(StoreProducts::COL_STOCK, '>', 0)
            ->get();
    }
  
}