<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\Store;
use App\Models\StoreProducts;
use App\Models\Unit;
use App\Models\UnitConversion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductScenariosSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasColumns('products', ['product_type', 'sell_unit_id'])) {
            throw new \RuntimeException('Missing columns product_type/sell_unit_id on products table. Run migrations first.');
        }

        $store = Store::find(1);
        if (!$store) {
            throw new \RuntimeException('Store with ID 1 was not found. Create first store before running this seeder.');
        }

        DB::transaction(function () use ($store): void {
            $unitPiece = Unit::firstOrCreate(
                ['name' => 'Unit', 'symbol' => 'u', 'store_id' => null],
                ['description' => 'Piece unit', 'is_active' => true]
            );

            $unitKg = Unit::firstOrCreate(
                ['name' => 'Kilogram', 'symbol' => 'kg', 'store_id' => null],
                ['description' => 'Weight in kilograms', 'is_active' => true]
            );

            $unitGram = Unit::firstOrCreate(
                ['name' => 'Gram', 'symbol' => 'g', 'store_id' => null],
                ['description' => 'Weight in grams', 'is_active' => true]
            );

            UnitConversion::updateOrCreate(
                [
                    'from_unit_id' => $unitKg->id,
                    'to_unit_id' => $unitGram->id,
                    'store_id' => $store->id,
                ],
                ['conversion_factor' => 1000]
            );

            // Scenario 1: Normal product where stock unit == sell unit
            $bottleWater = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'CASE-S1-WATER'],
                [
                    'name' => 'Water Bottle 50cl',
                    'description' => 'Scenario 1: same stock and sell unit (u -> u)',
                    'price' => 8,
                    'price_sell_1' => 8,
                    'price_buy' => 4,
                    'is_active' => true,
                    'is_stockable' => true,
                    'archive' => false,
                    'stock_alert' => 10,
                    'unit_id' => $unitPiece->id,
                    'sell_unit_id' => $unitPiece->id,
                    'product_type' => Product::TYPE_NORMAL,
                    'user_id' => $store->owner_id,
                ]
            );

            // Scenario 2: Normal product where stock unit != sell unit (kg -> g)
            $flour = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'CASE-S2-FLOUR'],
                [
                    'name' => 'Flour Bulk',
                    'description' => 'Scenario 2: stock in kg, sold in g',
                    'price' => 0.025,
                    'price_sell_1' => 0.025,
                    'price_buy' => 12,
                    'is_active' => true,
                    'is_stockable' => true,
                    'archive' => false,
                    'stock_alert' => 2,
                    'unit_id' => $unitKg->id,
                    'sell_unit_id' => $unitGram->id,
                    'product_type' => Product::TYPE_NORMAL,
                    'user_id' => $store->owner_id,
                ]
            );

            // Extra normal component candidate (same-unit)
            $cheeseSlice = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'CASE-COMP-CHEESE'],
                [
                    'name' => 'Cheese Slice',
                    'description' => 'Component candidate with same stock/sell unit',
                    'price' => 3,
                    'price_sell_1' => 3,
                    'price_buy' => 1.5,
                    'is_active' => true,
                    'is_stockable' => true,
                    'archive' => false,
                    'stock_alert' => 10,
                    'unit_id' => $unitPiece->id,
                    'sell_unit_id' => $unitPiece->id,
                    'product_type' => Product::TYPE_NORMAL,
                    'user_id' => $store->owner_id,
                ]
            );

            // Scenario 3: Product with components
            $sandwich = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'CASE-S3-SANDWICH'],
                [
                    'name' => 'Custom Sandwich',
                    'description' => 'Scenario 3: composed product with mixed component scenarios',
                    'price' => 35,
                    'price_sell_1' => 35,
                    'price_buy' => 0,
                    'is_active' => true,
                    'is_stockable' => true,
                    'archive' => false,
                    'stock_alert' => 5,
                    'unit_id' => $unitPiece->id,
                    'sell_unit_id' => $unitPiece->id,
                    'product_type' => Product::TYPE_COMPONENT,
                    'user_id' => $store->owner_id,
                ]
            );

            // Components for scenario 3
            ProductComponent::updateOrCreate(
                [
                    'product_id' => $sandwich->id,
                    'component_id' => $flour->id,
                ],
                [
                    'quantity' => 120, // 120g flour per sandwich
                    'unit_id' => $unitGram->id,
                    'note' => 'Flour measured in grams',
                ]
            );

            ProductComponent::updateOrCreate(
                [
                    'product_id' => $sandwich->id,
                    'component_id' => $cheeseSlice->id,
                ],
                [
                    'quantity' => 2,
                    'unit_id' => $unitPiece->id,
                    'note' => 'Two slices per sandwich',
                ]
            );

            // Ensure stock entries exist in store_products for testing order deduction
            $this->seedStoreProduct($store->id, $bottleWater->id, 8, 4, 250);
            $this->seedStoreProduct($store->id, $flour->id, 0.025, 12, 80); // 80kg stock
            $this->seedStoreProduct($store->id, $cheeseSlice->id, 3, 1.5, 300);
            $this->seedStoreProduct($store->id, $sandwich->id, 35, 0, 9999); // virtual stock for composed SKU visibility
        });
    }

    private function seedStoreProduct(int $storeId, int $productId, float $price, float $cost, float $stock): void
    {
        StoreProducts::updateOrCreate(
            [
                'store_id' => $storeId,
                'product_id' => $productId,
            ],
            [
                'price' => $price,
                'cost' => $cost,
                'stock' => $stock,
            ]
        );
    }
}
