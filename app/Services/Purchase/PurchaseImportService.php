<?php

namespace App\Services\Purchase;

use App\Enums\EnumOrderStatue;
use App\Models\Category;
use App\Models\OrderPurchase;
use App\Models\OrderPurchaseItems;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Repositories\Purchase\PurchaseRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseImportService
{
    public function __construct(
        private readonly PurchaseRepository $purchaseRepository,
    ) {}

    /**
     * Import purchase order with automatic product and category creation
     * Creates as draft (Brouillon) without updating stock
     * 
     * @param array $data
     * @return OrderPurchase
     * @throws \Exception
     */
    public function importPurchase(array $data): OrderPurchase
    {
        return DB::transaction(function () use ($data) {
            $storeId = currentStoreId();
            $userId = auth()->id();

            // Create the purchase order as draft (Brouillon)
            $purchase = $this->purchaseRepository->create([
                OrderPurchase::COL_ORDER_NUMBER => 'Brouillon',
                OrderPurchase::COL_SUPPLIER_ID => $data['supplier_id'],
                OrderPurchase::COL_REFERENCE => $data['reference'] ?? null,
                OrderPurchase::COL_PUBLIC_NOTE => $data['note'] ?? null,
                OrderPurchase::COL_STATUS => EnumOrderStatue::PENDING->value,
                OrderPurchase::COL_USER_ID => $userId,
                OrderPurchase::COL_STORE_ID => $storeId,
                'created_at' => $data['purchase_date'],
            ]);

            // Process each product
            foreach ($data['products'] as $productData) {
                // Find or create the product
                $product = $this->findOrCreateProduct($productData, $storeId, $userId);

                // Create purchase item
                $purchase->orderItems()->create([
                    OrderPurchaseItems::COL_PRODUCT_ID => $product->id,
                    OrderPurchaseItems::COL_STORE_ID => $storeId,
                    OrderPurchaseItems::COL_NAME => $productData['product_name'],
                    OrderPurchaseItems::COL_QUANTITY => $productData['quantity'],
                    OrderPurchaseItems::COL_PRICE => $productData['purchase_price'],
                    OrderPurchaseItems::COL_TOTAL => $productData['quantity'] * $productData['purchase_price'],
                ]);
            }

            return $purchase->fresh(['orderItems.product', 'supplier']);
        });
    }

    /**
     * Find product by codebar or supplier code, or create new one
     * 
     * @param array $productData
     * @param int $storeId
     * @param int $userId
     * @return Product
     */
    private function findOrCreateProduct(array $productData, int $storeId, int $userId): Product
    {
        $product = null;

        // Try to find by barcode first
        if (!empty($productData['codebar'])) {
            $barcode = ProductBarcode::where(ProductBarcode::COL_BARCODE, $productData['codebar'])->first();
            if ($barcode) {
                $product = $barcode->product;
            }
        }

        // If not found and supplier code exists, try to find by supplier code
        if (!$product && !empty($productData['code_supplier'])) {
            $product = Product::where(Product::COL_SUPPLIER_CODE, $productData['code_supplier'])
                ->where(Product::COL_STORE_ID, $storeId)
                ->first();
        }

        // If product found by barcode or supplier code, update it if import data has new barcode or purchase price
        if ($product) {
            $this->updateExistingProductFromImport($product, $productData);
            return $product;
        }

        // If product not found, create a new one
        if (!$product) {
            // Handle category
            $categoryId = null;
            if (!empty($productData['category'])) {
                $category = $this->findOrCreateCategory($productData['category'], $storeId, $userId);
                $categoryId = $category->id;
            }

            // Create the product
            $product = Product::create([
                Product::COL_NAME => $productData['product_name'],
                Product::COL_REFERENCE => $productData['code_supplier'] ?? null,
                Product::COL_SUPPLIER_CODE => $productData['code_supplier'] ?? null,
                Product::COL_SLUG => Str::slug($productData['product_name']),
                Product::COL_PRICE => $productData['sell_price'] ?? 0,
                Product::COL_PRICE_BUY => $productData['purchase_price'],
                Product::COL_IS_ACTIVE => true,
                Product::COL_IS_STOCKABLE => true,
                Product::COL_CATEGORY_ID => $categoryId,
                Product::COL_USER_ID => $userId,
                Product::COL_STORE_ID => $storeId,
            ]);

            // Create barcode if provided
            if (!empty($productData['codebar'])) {
                ProductBarcode::create([
                    ProductBarcode::COL_PRODUCT_ID => $product->id,
                    ProductBarcode::COL_BARCODE => $productData['codebar'],
                    ProductBarcode::COL_IS_PRIMARY => true,
                ]);
            }
        }

        return $product;
    }

    /**
     * Update an existing product from import data.
     *
     * @param Product $product
     * @param array $productData
     * @return void
     */
    private function updateExistingProductFromImport(Product $product, array $productData): void
    {
        $needsSave = false;

        if (!empty($productData['purchase_price']) && $product->price_buy != $productData['purchase_price']) {
            $product->price_buy = $productData['purchase_price'];
            $needsSave = true;
        }

        if (!empty($productData['code_supplier']) && empty($product->supplier_code)) {
            $product->supplier_code = $productData['code_supplier'];
            $needsSave = true;
        }

        if (!empty($productData['codebar'])) {
            $hasBarcode = $product->barcodes()
                ->where(ProductBarcode::COL_BARCODE, $productData['codebar'])
                ->exists();

            if (!$hasBarcode) {
                $product->barcodes()->create([
                    ProductBarcode::COL_BARCODE => $productData['codebar'],
                    ProductBarcode::COL_IS_PRIMARY => !$product->barcodes()->exists(),
                ]);
            }
        }

        if ($needsSave) {
            $product->save();
        }
    }

    /**
     * Find or create category by name
     * 
     * @param string $categoryName
     * @param int $storeId
     * @param int $userId
     * @return Category
     */
    private function findOrCreateCategory(string $categoryName, int $storeId, int $userId): Category
    {
        // Try to find existing category
        $category = Category::where(Category::COL_NAME, $categoryName)
            ->first();

        // If not found, create new category
        if (!$category) {
            $category = Category::create([
                Category::COL_NAME => $categoryName,
                Category::COL_SLUG => Str::slug($categoryName),
                Category::COL_USER_ID => $userId,
                Category::COL_STORE_ID => $storeId,
            ]);
        }

        return $category;
    }
}
