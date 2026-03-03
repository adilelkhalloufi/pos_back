<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProducts;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Store\StoreRepository;
// use App\Services\Brand\BrandService;
use App\Services\Product\Exceptions\ProductNotFoundException;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly StoreRepository $storeRepository,
        // private readonly BrandService $brandService
    ) {}


    public function findById(int $id)
    {
        return $this->productRepository->find($id);
    }

    public function findwithRelations(int $id)
    {
        $product = $this->productRepository->findWith($id, Product::COL_ID, [
            'purchase.order' => fn($query) => $query->orderBy('id', 'desc')->limit(3),
            'sales.order' => fn($query) => $query->orderBy('id', 'desc')->limit(3),
            'brand',
            'category',
        ]);
        if (!$product instanceof Product) {
            throw new ProductNotFoundException();
        }
        return $product;
    }

    public function create(array $attributes): ?Product
    {
        // i shiuld create product related to store and user and check if there is other store related to this store and add it to to other store 



        if (isset($attributes[Product::COL_IMAGE])) {
            $imagePath = $attributes[Product::COL_IMAGE]->store('products', 'public');
            $attributes[Product::COL_IMAGE] = $imagePath;
        }

        $product = $this->productRepository->create([
            Product::COL_NAME => $attributes[Product::COL_NAME],
            Product::COL_DESCRIPTION => $attributes[Product::COL_DESCRIPTION] ?? null,
            Product::COL_PRICE => $attributes[Product::COL_PRICE],
            Product::COL_CODEBAR => $attributes[Product::COL_CODEBAR] ?? null,
            Product::COL_IMAGE => $attributes[Product::COL_IMAGE] ?? null,
            Product::COL_ARCHIVE => $attributes[Product::COL_ARCHIVE] ?? false,
            Product::COL_CATEGORY_ID => $attributes[Product::COL_CATEGORY_ID] ?? null,
            Product::COL_BRAND_ID => $attributes[Product::COL_BRAND_ID] ?? null,
            Product::COL_STOCK_MAX => $attributes[Product::COL_STOCK_MAX] ?? null,
            Product::COL_STOCK_MIN => $attributes[Product::COL_STOCK_MIN] ?? null,
            Product::COL_IS_ACTIVE => $attributes[Product::COL_IS_ACTIVE] ?? true,
            Product::COL_USER_ID => auth()->id(),
            Product::COL_STORE_ID => currentStoreId(),
        ]);

        StoreProducts::create([
            StoreProducts::COL_STORE_ID => currentStoreId(),
            StoreProducts::COL_PRODUCT_ID => $product->id,
            StoreProducts::COL_PRICE => $attributes[Product::COL_PRICE],
        ]);

        // search for all stores realtead to this store
        $relatedStores = $this->storeRepository->findbyfield(currentStore()->owner_id, Store::COL_OWNER_ID);

        foreach ($relatedStores as $store) {
            if ($store->id != currentStoreId()) {
                StoreProducts::create([
                    StoreProducts::COL_STORE_ID => $store->id,
                    StoreProducts::COL_PRODUCT_ID => $product->id,
                    StoreProducts::COL_PRICE => $attributes[Product::COL_PRICE],
                    StoreProducts::COL_STOCK => 0,
                ]);
            }
        }

        return $product;
    }

    public function update(int $id, array $attributes): ?bool
    {
        if (isset($attributes[Product::COL_IMAGE])) {
            $imagePath = $attributes[Product::COL_IMAGE]->store('products', 'public');
            $attributes[Product::COL_IMAGE] = $imagePath;
        }

        $product = $this->productRepository->find($id);

        if (!$product instanceof Product) {
            throw new ProductNotFoundException();
        }


        $product = $this->productRepository->update($id, [
            Product::COL_NAME => $attributes[Product::COL_NAME] ?? $product->name,
            Product::COL_REFERENCE => $attributes[Product::COL_REFERENCE] ?? $product->reference,
            Product::COL_DESCRIPTION => $attributes[Product::COL_DESCRIPTION] ?? $product->description,
            Product::COL_SLUG => $attributes[Product::COL_SLUG] ?? $product->slug,
            Product::COL_PRICE => $attributes[Product::COL_PRICE] ?? $product->price,
            Product::COL_CODEBAR => $attributes[Product::COL_CODEBAR] ?? $product->codebar,
            Product::COL_IMAGE => $attributes[Product::COL_IMAGE] ?? $product->image,
            Product::COL_STOCK_MAX => $attributes[Product::COL_STOCK_MAX] ?? $product->stock_max,
            Product::COL_STOCK_MIN => $attributes[Product::COL_STOCK_MIN] ?? $product->stock_min,
            Product::COL_ARCHIVE => (bool) ($attributes[Product::COL_ARCHIVE] ?? $product->archive),
            Product::COL_IS_ACTIVE => (bool) ($attributes[Product::COL_IS_ACTIVE] ?? $product->is_active),
            Product::COL_CATEGORY_ID => $attributes[Product::COL_CATEGORY_ID] ?? $product->category_id,
            Product::COL_BRAND_ID => $attributes[Product::COL_BRAND_ID] ?? $product->brand_id,
        ]);

        return $product;
    }

    /**
     * Get products for POS with stock information
     * 
     * @param int $storeId
     * @param string|null $search
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getProductsForPOS(int $storeId, ?string $search = null)
    {
        return $this->productRepository->getProductsForPOS($storeId, $search);
    }
}
