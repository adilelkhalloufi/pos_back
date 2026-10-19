<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\Store;
use App\Models\StoreProducts;
use App\Models\Unit;
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

        $store = Store::first();
        if (!$store) {
            throw new \RuntimeException('No store found. Create a store before running this seeder.');
        }

        DB::transaction(function () use ($store): void {
            $unitPiece = Unit::firstOrCreate(
                ['name' => 'Piece', 'symbol' => 'u', 'store_id' => null],
                ['description' => 'Piece unit', 'is_active' => true]
            );

            $pijama1 = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'CLOTH-PIJAMA-1'],
                [
                    'name' => 'Pijama 1',
                    'description' => 'Basic pijama model used in the winter pack.',
                    'price' => 120,
                    'price_sell_1' => 120,
                    'price_buy' => 70,
                    'is_active' => true,
                    'is_stockable' => true,
                    'archive' => false,
                    'stock_alert' => 5,
                    'unit_id' => $unitPiece->id,
                    'sell_unit_id' => $unitPiece->id,
                    'product_type' => Product::TYPE_NORMAL,
                    'user_id' => $store->owner_id,
                ]
            );

            $pijama2 = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'CLOTH-PIJAMA-2'],
                [
                    'name' => 'Pijama 2',
                    'description' => 'Second pijama model used in the winter pack.',
                    'price' => 130,
                    'price_sell_1' => 130,
                    'price_buy' => 75,
                    'is_active' => true,
                    'is_stockable' => true,
                    'archive' => false,
                    'stock_alert' => 5,
                    'unit_id' => $unitPiece->id,
                    'sell_unit_id' => $unitPiece->id,
                    'product_type' => Product::TYPE_NORMAL,
                    'user_id' => $store->owner_id,
                ]
            );

            $pijama3 = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'CLOTH-PIJAMA-3'],
                [
                    'name' => 'Pijama 3',
                    'description' => 'Third pijama model used in the winter pack.',
                    'price' => 140,
                    'price_sell_1' => 140,
                    'price_buy' => 80,
                    'is_active' => true,
                    'is_stockable' => true,
                    'archive' => false,
                    'stock_alert' => 5,
                    'unit_id' => $unitPiece->id,
                    'sell_unit_id' => $unitPiece->id,
                    'product_type' => Product::TYPE_NORMAL,
                    'user_id' => $store->owner_id,
                ]
            );

            $packWinter = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'PACK-WINTER'],
                [
                    'name' => 'Pack Winter',
                    'description' => 'Composed winter pack including Pijama 1, Pijama 2, and Pijama 3.',
                    'price' => 360,
                    'price_sell_1' => 360,
                    'price_buy' => 0,
                    'is_active' => true,
                    'is_stockable' => true,
                    'archive' => false,
                    'stock_alert' => 3,
                    'unit_id' => $unitPiece->id,
                    'sell_unit_id' => $unitPiece->id,
                    'product_type' => Product::TYPE_COMPONENT,
                    'user_id' => $store->owner_id,
                ]
            );

            ProductComponent::updateOrCreate(
                ['product_id' => $packWinter->id, 'component_id' => $pijama1->id],
                ['quantity' => 1, 'unit_id' => $unitPiece->id, 'note' => 'Pijama 1 included in the winter pack']
            );

            ProductComponent::updateOrCreate(
                ['product_id' => $packWinter->id, 'component_id' => $pijama2->id],
                ['quantity' => 1, 'unit_id' => $unitPiece->id, 'note' => 'Pijama 2 included in the winter pack']
            );

            ProductComponent::updateOrCreate(
                ['product_id' => $packWinter->id, 'component_id' => $pijama3->id],
                ['quantity' => 1, 'unit_id' => $unitPiece->id, 'note' => 'Pijama 3 included in the winter pack']
            );

            $this->seedStoreProduct($store->id, $pijama1->id, 120, 70, 20);
            $this->seedStoreProduct($store->id, $pijama2->id, 130, 75, 18);
            $this->seedStoreProduct($store->id, $pijama3->id, 140, 80, 15);
            $this->seedStoreProduct($store->id, $packWinter->id, 360, 0, 9999);
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
