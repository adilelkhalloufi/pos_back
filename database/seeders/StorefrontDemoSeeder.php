<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreProducts;
use App\Models\StorefrontOrder;
use App\Models\StorefrontProduct;
use App\Models\Unit;
use App\Services\Storefront\StorefrontOrderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StorefrontDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (
            !Schema::hasTable('storefront_products')
            || !Schema::hasTable('storefront_orders')
            || !Schema::hasTable('product_variants')
        ) {
            throw new \RuntimeException('Missing storefront tables. Run migrations first.');
        }

        $store = Store::first();
        if (!$store instanceof Store) {
            throw new \RuntimeException('No store found. Create a store before running storefront demo seeder.');
        }

        DB::transaction(function () use ($store): void {
            $this->seedStorefrontSettings($store);

            $unitPiece = Unit::firstOrCreate(
                ['name' => 'Piece', 'symbol' => 'u', 'store_id' => null],
                ['description' => 'Piece unit', 'is_active' => true]
            );

            $tshirt = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'SF-TSHIRT-001'],
                [
                    'name' => 'T-Shirt Classic',
                    'description' => 'Classic cotton t-shirt for daily wear.',
                    'price' => 150,
                    'price_sell_1' => 150,
                    'price_buy' => 85,
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

            $ring = Product::updateOrCreate(
                ['store_id' => $store->id, 'reference' => 'SF-RING-001'],
                [
                    'name' => 'Elegant Ring',
                    'description' => 'Elegant 18K ring with gemstone options.',
                    'price' => 3200,
                    'price_sell_1' => 3200,
                    'price_buy' => 2500,
                    'is_active' => true,
                    'is_stockable' => true,
                    'archive' => false,
                    'stock_alert' => 1,
                    'unit_id' => $unitPiece->id,
                    'sell_unit_id' => $unitPiece->id,
                    'product_type' => Product::TYPE_NORMAL,
                    'user_id' => $store->owner_id,
                ]
            );

            StoreProducts::firstOrCreate(
                ['store_id' => $store->id, 'product_id' => $tshirt->id],
                ['price' => 150, 'cost' => 85, 'stock' => 30]
            );

            StoreProducts::firstOrCreate(
                ['store_id' => $store->id, 'product_id' => $ring->id],
                ['price' => 3200, 'cost' => 2500, 'stock' => 8]
            );

            $this->seedTshirtVariants($store->id, $tshirt->id);
            $this->seedRingVariants($store->id, $ring->id);

            StorefrontProduct::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'product_id' => $tshirt->id,
                ],
                [
                    'is_published' => true,
                    'sort_order' => 1,
                    'short_description' => 'Best seller classic t-shirt',
                    'cover_image' => null,
                ]
            );

            StorefrontProduct::updateOrCreate(
                [
                    'store_id' => $store->id,
                    'product_id' => $ring->id,
                ],
                [
                    'is_published' => true,
                    'sort_order' => 2,
                    'short_description' => 'Premium jewelry ring collection',
                    'cover_image' => null,
                ]
            );

            $this->seedOneDemoOrder($store);
        });
    }

    private function seedStorefrontSettings(Store $store): void
    {
        if (empty($store->public_slug)) {
            $store->update([
                Store::COL_PUBLIC_SLUG => 'demo-store-' . $store->id,
            ]);
        }

        $store->update([
            Store::COL_STOREFRONT_ENABLED => true,
            Store::COL_STOREFRONT_TITLE => 'Demo Fashion & Jewelry Store',
            Store::COL_STOREFRONT_DESCRIPTION => 'Demo storefront for clothing and jewelry ordering.',
        ]);
    }

    private function seedTshirtVariants(int $storeId, int $productId): void
    {
        ProductVariant::firstOrCreate(
            ['store_id' => $storeId, 'product_id' => $productId, 'sku' => 'TS-BLK-M'],
            [
                'color' => 'Black',
                'size' => 'M',
                'material' => 'Cotton',
                'attributes' => ['fit' => 'regular', 'season' => 'all'],
                'price' => 150,
                'stock' => 12,
                'is_active' => true,
            ]
        );

        ProductVariant::firstOrCreate(
            ['store_id' => $storeId, 'product_id' => $productId, 'sku' => 'TS-WHT-L'],
            [
                'color' => 'White',
                'size' => 'L',
                'material' => 'Cotton',
                'attributes' => ['fit' => 'regular', 'season' => 'all'],
                'price' => 155,
                'stock' => 10,
                'is_active' => true,
            ]
        );
    }

    private function seedRingVariants(int $storeId, int $productId): void
    {
        ProductVariant::firstOrCreate(
            ['store_id' => $storeId, 'product_id' => $productId, 'sku' => 'RING-GOLD-52'],
            [
                'color' => 'Gold',
                'size' => '52',
                'material' => '18K Gold',
                'gemstone' => 'Diamond',
                'weight_grams' => 5.2,
                'attributes' => ['style' => 'classic'],
                'price' => 3200,
                'stock' => 3,
                'is_active' => true,
            ]
        );

        ProductVariant::firstOrCreate(
            ['store_id' => $storeId, 'product_id' => $productId, 'sku' => 'RING-SILVER-54'],
            [
                'color' => 'Silver',
                'size' => '54',
                'material' => '925 Silver',
                'gemstone' => 'Zircon',
                'weight_grams' => 4.8,
                'attributes' => ['style' => 'modern'],
                'price' => 1400,
                'stock' => 4,
                'is_active' => true,
            ]
        );
    }

    private function seedOneDemoOrder(Store $store): void
    {
        $alreadySeeded = StorefrontOrder::query()
            ->where('store_id', $store->id)
            ->where('customer_phone', '+212600000111')
            ->where('note', 'SEEDER_DEMO_ORDER')
            ->exists();

        if ($alreadySeeded) {
            return;
        }

        $tshirtVariant = ProductVariant::query()
            ->where('store_id', $store->id)
            ->where('sku', 'TS-BLK-M')
            ->first();

        $ringVariant = ProductVariant::query()
            ->where('store_id', $store->id)
            ->where('sku', 'RING-GOLD-52')
            ->first();

        if (!$tshirtVariant instanceof ProductVariant || !$ringVariant instanceof ProductVariant) {
            throw new \RuntimeException('Missing demo variants for storefront order seeding.');
        }

        /** @var StorefrontOrderService $service */
        $service = app(StorefrontOrderService::class);

        $service->create($store, [
            'customer_name' => 'Demo Customer',
            'customer_phone' => '+212600000111',
            'customer_email' => 'demo.customer@example.com',
            'customer_address' => 'Casablanca',
            'note' => 'SEEDER_DEMO_ORDER',
            'currency' => 'MAD',
            'items' => [
                [
                    'product_id' => $tshirtVariant->product_id,
                    'variant_id' => $tshirtVariant->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $ringVariant->product_id,
                    'variant_id' => $ringVariant->id,
                    'quantity' => 1,
                ],
            ],
        ]);
    }
}
