<?php

namespace App\Repositories\Product;

use App\Models\Product;
use App\Models\StoreProducts;
use App\Repositories\BaseRepository;

class ProductRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Product::class;
    }

    /**
     * Get products for POS with store quantities
     * 
     * @param int $storeId
     * @param string|null $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductsForPOS(int $storeId, ?string $search = null)
    {
        $query = $this->getQueryBuilder()
            ->with([
                'category',
                'unit',
                'store' => function($query) use ($storeId) {
                    $query->where(StoreProducts::COL_STORE_ID, $storeId);
                }
            ])
            ->whereHas('store', function($query) use ($storeId) {
                $query->where(StoreProducts::COL_STORE_ID, $storeId)
                      ->where(StoreProducts::COL_STOCK, '>', 0);
            })
            ->where(Product::COL_ARCHIVE, false);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where(Product::COL_NAME, 'LIKE', "%{$search}%")
                  ->orWhere(Product::COL_CODEBAR, 'LIKE', "%{$search}%")
                  ->orWhere(Product::COL_REFERENCE, 'LIKE', "%{$search}%");
            });
        }

        return $query->get();
    }
  
}