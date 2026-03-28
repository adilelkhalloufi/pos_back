<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProducts;
use App\Models\PriceChangeLog;
use App\Models\ProductBarcode;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Store\StoreRepository;
use App\Services\Product\Exceptions\ProductNotFoundException;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly StoreRepository $storeRepository,
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
            'barcodes',
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
            Product::COL_NAME             => $attributes[Product::COL_NAME],
            Product::COL_DESCRIPTION      => $attributes[Product::COL_DESCRIPTION] ?? null,
            Product::COL_PRICE            => $attributes['price_sell_1'] ?? $attributes[Product::COL_PRICE] ?? null,
            Product::COL_PRICE_BUY        => $attributes['price_buy'] ?? null,
            Product::COL_PRICE_SELL_1     => $attributes['price_sell_1'] ?? $attributes[Product::COL_PRICE] ?? null,
            Product::COL_SUPPLIER_CODE    => $attributes['supplier_code'] ?? null,
            Product::COL_IMAGE            => $attributes[Product::COL_IMAGE] ?? null,
            Product::COL_ARCHIVE          => $attributes[Product::COL_ARCHIVE] ?? false,
            Product::COL_CATEGORY_ID      => $attributes[Product::COL_CATEGORY_ID] ?? null,
            Product::COL_STOCK_ALERT        => $attributes[Product::COL_STOCK_ALERT] ?? null,
            Product::COL_IS_ACTIVE        => $attributes[Product::COL_IS_ACTIVE] ?? true,
            Product::COL_IS_STOCKABLE     => $attributes['is_stockable'] ?? true,
            Product::COL_UNIT_ID          => $attributes['unit_id'] ?? null,
            Product::COL_PRINT_PROFILE_ID => $attributes['print_profile_id'] ?? null,
            Product::COL_USER_ID          => auth()->id(),
            Product::COL_STORE_ID         => currentStoreId(),
        ]);

        $sellPrice = $attributes['price_sell_1'] ?? $attributes[Product::COL_PRICE] ?? null;

        StoreProducts::create([
            StoreProducts::COL_STORE_ID   => currentStoreId(),
            StoreProducts::COL_PRODUCT_ID => $product->id,
            StoreProducts::COL_PRICE      => $sellPrice,
        ]);

        // Propagate to all stores owned by same owner
        $relatedStores = $this->storeRepository->findbyfield(currentStore()->owner_id, Store::COL_OWNER_ID);

        foreach ($relatedStores as $store) {
            if ($store->id != currentStoreId()) {
                StoreProducts::create([
                    StoreProducts::COL_STORE_ID   => $store->id,
                    StoreProducts::COL_PRODUCT_ID => $product->id,
                    StoreProducts::COL_PRICE      => $sellPrice,
                    StoreProducts::COL_STOCK      => 0,
                ]);
            }
        }

        // Handle barcodes - collect from both codebar and barcodes array
        $barcodesToSave = [];

        // If barcodes array is provided
        if (isset($attributes['barcodes'])) {
            // Handle if it's sent as JSON string from frontend
            if (is_string($attributes['barcodes'])) {
                $attributes['barcodes'] = json_decode($attributes['barcodes'], true) ?? [];
            }

            if (is_array($attributes['barcodes'])) {
                foreach ($attributes['barcodes'] as $barcode) {
                    // Handle object format {"barcode": "123"}
                    if (is_array($barcode) && isset($barcode['barcode'])) {
                        $barcode = $barcode['barcode'];
                    }

                    if (!empty($barcode) && is_string($barcode)) {
                        $barcodesToSave[] = trim($barcode);
                    }
                }
            }
        }

        // If single codebar is provided and not already in array, add it as primary
        if (!empty($attributes['codebar'])) {
            $codebar = trim($attributes['codebar']);
            if (!in_array($codebar, $barcodesToSave)) {
                array_unshift($barcodesToSave, $codebar); // Add as first (primary)
            }
        }

        // Remove duplicates and save barcodes
        $barcodesToSave = array_values(array_unique($barcodesToSave));
        foreach ($barcodesToSave as $index => $barcode) {
            ProductBarcode::create([
                'product_id' => $product->id,
                'barcode' => $barcode,
                'is_primary' => $index === 0, // First barcode is primary
            ]);
        }

        return $product;
    }

    public function update(int $id, array $attributes): ?Product
    {
        if (isset($attributes[Product::COL_IMAGE])) {
            $imagePath = $attributes[Product::COL_IMAGE]->store('products', 'public');
            $attributes[Product::COL_IMAGE] = $imagePath;
        }

        $product = $this->productRepository->find($id);
        if (!$product instanceof Product) {
            throw new ProductNotFoundException();
        }
        $this->productRepository->update($id, [
            Product::COL_NAME             => $attributes[Product::COL_NAME] ?? $product->name,
            Product::COL_REFERENCE        => $attributes[Product::COL_REFERENCE] ?? $product->reference,
            Product::COL_SUPPLIER_CODE    => $attributes['supplier_code'] ?? $product->supplier_code,
            Product::COL_DESCRIPTION      => $attributes[Product::COL_DESCRIPTION] ?? $product->description,
            Product::COL_SLUG             => $attributes[Product::COL_SLUG] ?? $product->slug,
            Product::COL_PRICE            => $attributes['price_sell_1'] ?? $attributes[Product::COL_PRICE] ?? $product->price,
            Product::COL_PRICE_BUY        => $attributes['price_buy'] ?? $product->price_buy,
            Product::COL_PRICE_SELL_1     => $attributes['price_sell_1'] ?? $product->price_sell_1,
            // Product::COL_CODEBAR          => $attributes[Product::COL_CODEBAR] ?? $product->codebar,
            Product::COL_IMAGE            => $attributes[Product::COL_IMAGE] ?? $product->image,
            Product::COL_STOCK_ALERT      => $attributes[Product::COL_STOCK_ALERT] ?? $product->stock_alert,
            Product::COL_ARCHIVE          => (bool) ($attributes[Product::COL_ARCHIVE] ?? $product->archive),
            Product::COL_IS_ACTIVE        => (bool) ($attributes[Product::COL_IS_ACTIVE] ?? $product->is_active),
            Product::COL_IS_STOCKABLE     => (bool) ($attributes['is_stockable'] ?? $product->is_stockable),
            Product::COL_CATEGORY_ID      => $attributes[Product::COL_CATEGORY_ID] ?? $product->category_id,
            Product::COL_UNIT_ID          => $attributes['unit_id'] ?? $product->unit_id,
            Product::COL_PRINT_PROFILE_ID => $attributes['print_profile_id'] ?? $product->print_profile_id,
        ]);

        // Log price changes if any price field changed
        $priceFields = ['price_buy', 'price_sell_1'];
        $fresh = $this->productRepository->find($id);
        foreach ($priceFields as $field) {
            if (isset($attributes[$field]) && (float) $attributes[$field] !== (float) ($product->$field ?? 0)) {
                PriceChangeLog::create([
                    PriceChangeLog::COL_PRODUCT_ID => $id,
                    PriceChangeLog::COL_USER_ID    => auth()->id(),
                    PriceChangeLog::COL_FIELD      => $field,
                    PriceChangeLog::COL_OLD_VALUE  => $product->$field,
                    PriceChangeLog::COL_NEW_VALUE  => (float) $attributes[$field],
                    PriceChangeLog::COL_STORE_ID   => currentStoreId(),
                ]);
            }
        }

        // Handle barcodes update - collect from both codebar and barcodes array
        if (isset($attributes['barcodes']) || isset($attributes['codebar'])) {
            $barcodesToSave = [];

            // If barcodes array is provided
            if (isset($attributes['barcodes'])) {
                // Handle if it's sent as JSON string from frontend
                if (is_string($attributes['barcodes'])) {
                    $attributes['barcodes'] = json_decode($attributes['barcodes'], true) ?? [];
                }

                if (is_array($attributes['barcodes'])) {
                    foreach ($attributes['barcodes'] as $barcode) {
                        // Handle object format {"barcode": "123"}
                        if (is_array($barcode) && isset($barcode['barcode'])) {
                            $barcode = $barcode['barcode'];
                        }

                        if (!empty($barcode) && is_string($barcode)) {
                            $barcodesToSave[] = trim($barcode);
                        }
                    }
                }
            }

            // If single codebar is provided and not already in array, add it as primary
            if (isset($attributes['codebar']) && !empty($attributes['codebar'])) {
                $codebar = trim($attributes['codebar']);
                if (!in_array($codebar, $barcodesToSave)) {
                    array_unshift($barcodesToSave, $codebar); // Add as first (primary)
                }
            }

            // Remove duplicates
            $barcodesToSave = array_values(array_unique($barcodesToSave));

            // Only update if we have barcodes to save
            if (!empty($barcodesToSave)) {
                // Delete existing barcodes
                ProductBarcode::where('product_id', $id)->delete();

                // Add new barcodes
                foreach ($barcodesToSave as $index => $barcode) {
                    ProductBarcode::create([
                        'product_id' => $id,
                        'barcode' => $barcode,
                        'is_primary' => $index === 0, // First barcode is primary
                    ]);
                }
            }
        }

        return $fresh;
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
