<?php

namespace App\Services\StoreProduct;

use App\Models\StoreProducts;
use App\Repositories\StoreProduct\StoreProductRepository;
use App\Services\StoreProduct\Exceptions\StoreProductNotFoundException;

class StoreProductService
{
    public function __construct(
        private readonly StoreProductRepository $storeProductRepository
    ) {}

    /**
     * Find store product by ID
     */
    public function findById(int $id): ?StoreProducts
    {
        return $this->storeProductRepository->find($id);
    }

    /**
     * Find store product by store and product ID
     */
    public function findByStoreAndProduct(int $storeId, int $productId): ?StoreProducts
    {
        return $this->storeProductRepository->findByStoreAndProduct($storeId, $productId);
    }

    /**
     * Get all products for a specific store
     */
    public function getStoreProducts(int $storeId, array $relations = [])
    {
        return $this->storeProductRepository->getByStore($storeId, $relations);
    }

    /**
     * Get in-stock products for a store (for POS)
     */
    public function getInStockProducts(int $storeId, ?string $search = null)
    {
        return $this->storeProductRepository->getInStockProducts($storeId, $search);
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $storeId, float $threshold = 10)
    {
        return $this->storeProductRepository->getLowStockProducts($storeId, $threshold);
    }

    /**
     * Create or update store product
     */
    public function createOrUpdate(array $attributes): StoreProducts
    {
        return $this->storeProductRepository->updateOrCreate(
            [
                StoreProducts::COL_STORE_ID => $attributes[StoreProducts::COL_STORE_ID],
                StoreProducts::COL_PRODUCT_ID => $attributes[StoreProducts::COL_PRODUCT_ID],
            ],
            [
                StoreProducts::COL_PRICE => $attributes[StoreProducts::COL_PRICE] ?? 0,
                StoreProducts::COL_COST => $attributes[StoreProducts::COL_COST] ?? 0,
                StoreProducts::COL_STOCK => $attributes[StoreProducts::COL_STOCK] ?? 0,
            ]
        );
    }

    /**
     * Update store product
     */
    public function update(int $id, array $attributes): bool
    {
        $storeProduct = $this->storeProductRepository->find($id);

        if (!$storeProduct) {
            throw new StoreProductNotFoundException();
        }

        return $this->storeProductRepository->update($id, [
            StoreProducts::COL_PRICE => $attributes[StoreProducts::COL_PRICE] ?? $storeProduct->{StoreProducts::COL_PRICE},
            StoreProducts::COL_COST => $attributes[StoreProducts::COL_COST] ?? $storeProduct->{StoreProducts::COL_COST},
            StoreProducts::COL_STOCK => $attributes[StoreProducts::COL_STOCK] ?? $storeProduct->{StoreProducts::COL_STOCK},
        ]);
    }

    /**
     * Update stock quantity
     */
    public function updateStock(int $storeId, int $productId, float $quantity, string $operation = 'set'): bool
    {
        return $this->storeProductRepository->updateStock($storeId, $productId, $quantity, $operation);
    }

    /**
     * Delete store product
     */
    public function delete(int $id): bool
    {
        return $this->storeProductRepository->delete($id);
    }
}
