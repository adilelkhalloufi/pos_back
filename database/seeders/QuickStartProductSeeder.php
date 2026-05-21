<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuickStartProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::query()->first();
        $user = User::query()->first();

        $categories = [
            'Meat' => 'Products from the meat category',
            'Grains' => 'Products from the grain category',
            'Vegetables' => 'Fresh vegetable products',
            'Oils' => 'Cooking oils and liquids',
        ];

        $categoryIds = [];
        foreach ($categories as $name => $description) {
            $category = Category::updateOrCreate(
                ['name' => $name],
                [
                    'slug' => Str::slug($name),
                    'description' => $description,
                    'store_id' => $store?->id,
                    'user_id' => $user?->id,
                ]
            );

            $categoryIds[$name] = $category->id;
        }

        $products = [
            [
                'name' => 'Chicken Breast',
                'reference' => 'REF-001',
                'description' => 'Fresh chicken breast for recipes',
                'category' => 'Meat',
                'unit_symbol' => 'kg',
                'price' => 12.00,
                'price_buy' => 8.00,
                'price_sell_1' => 12.00,
                'stock_alert' => 10,
                'is_active' => true,
                'archive' => false,
                'codebar' => '1234567890123',
                'barcodes' => ['1234567890123', '9876543210987'],
            ],
            [
                'name' => 'Rice',
                'reference' => 'REF-002',
                'description' => 'Long-grain white rice',
                'category' => 'Grains',
                'unit_symbol' => 'g',
                'price' => 2.50,
                'price_buy' => 1.50,
                'price_sell_1' => 2.50,
                'stock_alert' => 20,
                'is_active' => true,
                'archive' => false,
                'codebar' => '2234567890123',
                'barcodes' => ['2234567890123'],
            ],
            [
                'name' => 'Lettuce',
                'reference' => 'REF-003',
                'description' => 'Fresh lettuce for salads',
                'category' => 'Vegetables',
                'unit_symbol' => 'pc',
                'price' => 1.20,
                'price_buy' => 0.80,
                'price_sell_1' => 1.20,
                'stock_alert' => 15,
                'is_active' => true,
                'archive' => false,
                'codebar' => '3234567890123',
                'barcodes' => ['3234567890123'],
            ],
            [
                'name' => 'Tomatoes',
                'reference' => 'REF-004',
                'description' => 'Ripe tomatoes for cooking',
                'category' => 'Vegetables',
                'unit_symbol' => 'pc',
                'price' => 1.80,
                'price_buy' => 1.10,
                'price_sell_1' => 1.80,
                'stock_alert' => 15,
                'is_active' => true,
                'archive' => false,
                'codebar' => '4234567890123',
                'barcodes' => ['4234567890123'],
            ],
            [
                'name' => 'Olive Oil',
                'reference' => 'REF-005',
                'description' => 'Extra virgin olive oil',
                'category' => 'Oils',
                'unit_symbol' => 'L',
                'price' => 15.00,
                'price_buy' => 10.00,
                'price_sell_1' => 15.00,
                'stock_alert' => 5,
                'is_active' => true,
                'archive' => false,
                'codebar' => '5234567890123',
                'barcodes' => ['5234567890123'],
            ],
        ];

        foreach ($products as $item) {
            $unit = DB::table('units')->where('symbol', $item['unit_symbol'])->first();
            $product = Product::updateOrCreate(
                ['reference' => $item['reference']],
                [
                    'name' => $item['name'],
                    'slug' => Str::slug($item['name']),
                    'description' => $item['description'],
                    'category_id' => $categoryIds[$item['category']] ?? null,
                    'price' => $item['price'],
                    'price_buy' => $item['price_buy'],
                    'price_sell_1' => $item['price_sell_1'],
                    'stock_alert' => $item['stock_alert'],
                    'is_active' => $item['is_active'],
                    'archive' => $item['archive'],
                    'unit_id' => $unit?->id,
                    'supplier_code' => null,
                    'store_id' => $store?->id,
                    'user_id' => $user?->id,
                ]
            );

            if ($store) {
                DB::table('store_products')->updateOrInsert(
                    [
                        'store_id' => $store->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'price' => $product->price,
                        'cost' => $product->price_buy,
                        'stock' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
