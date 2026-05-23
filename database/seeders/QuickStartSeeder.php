<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\OrderPurchase;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Store;
use App\Models\StoreProduct;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuickStartSeeder extends Seeder
{
    /**
     * Run the Quick Start setup for testing sales
     * Creates: Products, Stock, Recipes, Menus, Categories, Menu Items
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            $this->command->info('🚀 Starting Quick Start Setup...');

            // Get or create default store
            $store = Store::first();
            if (!$store) {
                $this->command->error('❌ No store found! Please create a store first.');
                return;
            }

            // Get or create product category
            $category = Category::firstOrCreate(
                ['name' => 'Food Ingredients'],
                [
                    'description' => 'Ingredients for recipes',
                    'code' => 'FOOD-001'
                ]
            );

            $drinkCategory = Category::firstOrCreate(
                ['name' => 'Beverages'],
                [
                    'description' => 'Drinks and beverages',
                    'code' => 'BEV-001'
                ]
            );

            // Get units (assuming UnitsAndConversionsSeeder has been run)
            $unitKg = Unit::where('code', 'kg')->first();
            $unitGram = Unit::where('code', 'g')->first();
            $unitLiter = Unit::where('code', 'l')->first();
            $unitMl = Unit::where('code', 'ml')->first();
            $unitPiece = Unit::where('code', 'pcs')->first();

            if (!$unitKg || !$unitGram || !$unitLiter || !$unitPiece) {
                $this->command->error('❌ Units not found! Please run UnitsAndConversionsSeeder first.');
                $this->command->info('Run: php artisan db:seed --class=UnitsAndConversionsSeeder');
                return;
            }

            $this->command->info('');
            $this->command->info('📦 STEP 1: Creating Products...');

            // Create products
            $products = [
                [
                    'name' => 'Chicken Breast',
                    'reference' => 'REF-001',
                    'description' => 'Fresh chicken breast for recipes',
                    'category_id' => $category->id,
                    'unit_id' => $unitKg->id,
                    'price' => 12.00,
                    'price_buy' => 8.00,
                    'price_sell_1' => 12.00,
                    'stock_alert' => 10,
                    'is_active' => true,
                    'archive' => false,
                    'codebar' => '1234567890123',
                ],
                [
                    'name' => 'Rice',
                    'reference' => 'REF-002',
                    'description' => 'Long-grain white rice',
                    'category_id' => $category->id,
                    'unit_id' => $unitKg->id,
                    'price' => 2.50,
                    'price_buy' => 1.50,
                    'price_sell_1' => 2.50,
                    'stock_alert' => 20,
                    'is_active' => true,
                    'archive' => false,
                    'codebar' => '2234567890123',
                ],
                [
                    'name' => 'Lettuce',
                    'reference' => 'REF-003',
                    'description' => 'Fresh lettuce for salads',
                    'category_id' => $category->id,
                    'unit_id' => $unitKg->id,
                    'price' => 1.20,
                    'price_buy' => 0.80,
                    'price_sell_1' => 1.20,
                    'stock_alert' => 15,
                    'is_active' => true,
                    'archive' => false,
                    'codebar' => '3234567890123',
                ],
                [
                    'name' => 'Tomatoes',
                    'reference' => 'REF-004',
                    'description' => 'Ripe tomatoes for cooking',
                    'category_id' => $category->id,
                    'unit_id' => $unitKg->id,
                    'price' => 1.80,
                    'price_buy' => 1.10,
                    'price_sell_1' => 1.80,
                    'stock_alert' => 15,
                    'is_active' => true,
                    'archive' => false,
                    'codebar' => '4234567890123',
                ],
                [
                    'name' => 'Olive Oil',
                    'reference' => 'REF-005',
                    'description' => 'Extra virgin olive oil',
                    'category_id' => $category->id,
                    'unit_id' => $unitLiter->id,
                    'price' => 15.00,
                    'price_buy' => 10.00,
                    'price_sell_1' => 15.00,
                    'stock_alert' => 5,
                    'is_active' => true,
                    'archive' => false,
                    'codebar' => '5234567890123',
                ],
                [
                    'name' => 'Coca Cola',
                    'reference' => 'REF-006',
                    'description' => 'Refreshing cola drink 330ml',
                    'category_id' => $drinkCategory->id,
                    'unit_id' => $unitPiece->id,
                    'price' => 3.00,
                    'price_buy' => 1.50,
                    'price_sell_1' => 3.00,
                    'stock_alert' => 20,
                    'is_active' => true,
                    'archive' => false,
                    'codebar' => '6234567890123',
                ],
                [
                    'name' => 'Orange Juice',
                    'reference' => 'REF-007',
                    'description' => 'Fresh orange juice 250ml',
                    'category_id' => $drinkCategory->id,
                    'unit_id' => $unitPiece->id,
                    'price' => 4.00,
                    'price_buy' => 2.00,
                    'price_sell_1' => 4.00,
                    'stock_alert' => 15,
                    'is_active' => true,
                    'archive' => false,
                    'codebar' => '7234567890123',
                ],
            ];

            $createdProducts = [];
            foreach ($products as $productData) {
                $product = Product::create($productData);
                $createdProducts[$productData['name']] = $product;
                $this->command->info("   ✓ Created: {$product->name}");
            }

            $this->command->info('');
            $this->command->info('📥 STEP 3: Adding Stock (Purchase Orders)...');

            // Get or create supplier
            $supplier = Supplier::firstOrCreate(
                ['name' => 'Quick Start Supplier'],
                [
                    'email' => 'supplier@quickstart.com',
                    'phone' => '1234567890',
                    'address' => '123 Supplier St',
                ]
            );

            // Create purchase orders to add stock
            $purchases = [
                ['product' => 'Chicken Breast', 'quantity' => 50, 'price' => 8.00],
                ['product' => 'Rice', 'quantity' => 100, 'price' => 1.50],
                ['product' => 'Lettuce', 'quantity' => 20, 'price' => 0.80],
                ['product' => 'Tomatoes', 'quantity' => 30, 'price' => 1.10],
                ['product' => 'Olive Oil', 'quantity' => 10, 'price' => 10.00],
                ['product' => 'Coca Cola', 'quantity' => 100, 'price' => 1.50],
                ['product' => 'Orange Juice', 'quantity' => 80, 'price' => 2.00],
            ];

            foreach ($purchases as $purchaseData) {
                $product = $createdProducts[$purchaseData['product']];

                $purchase = OrderPurchase::create([
                    'store_id' => $store->id,
                    'supplier_id' => $supplier->id,
                    'reference' => 'PO-QS-' . rand(1000, 9999),
                    'date' => now(),
                    'status' => 'completed',
                    'total_amount' => $purchaseData['quantity'] * $purchaseData['price'],
                    'note' => 'Quick Start Setup',
                    'items' => [
                        [
                            'product_id' => $product->id,
                            'quantity' => $purchaseData['quantity'],
                            'unit_price' => $purchaseData['price'],
                            'total' => $purchaseData['quantity'] * $purchaseData['price'],
                        ]
                    ]
                ]);

                // Update store product stock
                StoreProduct::updateOrCreate(
                    [
                        'store_id' => $store->id,
                        'product_id' => $product->id,
                    ],
                    [
                        'stock' => DB::raw("stock + {$purchaseData['quantity']}"),
                        'cost' => $purchaseData['price'],
                    ]
                );

                $this->command->info("   ✓ Purchased: {$purchaseData['quantity']} {$product->unit->name} of {$product->name} @ \${$purchaseData['price']}");
            }

            $this->command->info('');
            $this->command->info('📖 STEP 4: Creating Recipe...');

            // Create recipe
            $recipe = Recipe::create([
                'store_id' => $store->id,
                'name' => 'Grilled Chicken Salad',
                'description' => 'Healthy grilled chicken with fresh vegetables',
                'instructions' => "1. Season and grill chicken\n2. Prepare salad base\n3. Combine and dress",
                'yield_quantity' => 1,
                'yield_unit_id' => $unitPiece->id,
                'preparation_time_minutes' => 15,
                'cooking_time_minutes' => 10,
                'skill_level' => 'intermediate',
                'is_active' => true,
            ]);

            $this->command->info("   ✓ Created recipe: {$recipe->name}");

            // Add ingredients to recipe
            $ingredients = [
                ['product' => 'Chicken Breast', 'quantity' => 0.25, 'unit' => $unitKg, 'waste' => 10],
                ['product' => 'Rice', 'quantity' => 0.15, 'unit' => $unitKg, 'waste' => 0],
                ['product' => 'Lettuce', 'quantity' => 0.1, 'unit' => $unitKg, 'waste' => 15],
                ['product' => 'Tomatoes', 'quantity' => 0.05, 'unit' => $unitKg, 'waste' => 5],
                ['product' => 'Olive Oil', 'quantity' => 0.02, 'unit' => $unitLiter, 'waste' => 0],
            ];

            foreach ($ingredients as $ingredientData) {
                $product = $createdProducts[$ingredientData['product']];

                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'product_id' => $product->id,
                    'quantity' => $ingredientData['quantity'],
                    'unit_id' => $ingredientData['unit']->id,
                    'waste_percentage' => $ingredientData['waste'],
                    'preparation_note' => 'Quick start ingredient',
                ]);

                $this->command->info("   ✓ Added: {$ingredientData['quantity']} {$ingredientData['unit']->name} {$product->name}");
            }

            // Calculate recipe cost
            $recipe->calculateCost();
            $this->command->info("   💰 Recipe total cost: \${$recipe->total_cost}");

            $this->command->info('');
            $this->command->info('🍽️ STEP 5: Creating Menu...');

            // Create menu
            $menu = Menu::create([
                'store_id' => $store->id,
                'name' => 'Lunch Menu',
                'description' => 'Available during lunch hours',
                'is_active' => true,
                'is_all_day' => false,
                'start_time' => '11:00:00',
                'end_time' => '15:00:00',
                'display_order' => 1,
            ]);

            $this->command->info("   ✓ Created menu: {$menu->name} (11:00 AM - 3:00 PM)");

            $this->command->info('');
            $this->command->info('📂 STEP 6: Creating Menu Categories...');

            // Create menu categories
            $mainCategory = MenuCategory::create([
                'menu_id' => $menu->id,
                'name' => 'Main Courses',
                'description' => 'Delicious main dishes',
                'is_active' => true,
                'display_order' => 1,
            ]);

            $drinkMenuCategory = MenuCategory::create([
                'menu_id' => $menu->id,
                'name' => 'Beverages',
                'description' => 'Refreshing drinks',
                'is_active' => true,
                'display_order' => 2,
            ]);

            $this->command->info("   ✓ Created category: {$mainCategory->name}");
            $this->command->info("   ✓ Created category: {$drinkMenuCategory->name}");

            $this->command->info('');
            $this->command->info('🍴 STEP 7: Creating Menu Items...');

            // Create menu items

            // Recipe-based menu item
            $menuItem1 = MenuItem::create([
                'store_id' => $store->id,
                'menu_category_id' => $mainCategory->id,
                'name' => 'Grilled Chicken Salad',
                'description' => 'Fresh salad with grilled chicken, rice, and vegetables',
                'price' => 12.99,
                'item_type' => 'recipe',
                'recipe_id' => $recipe->id,
                'is_active' => true,
                'is_available' => true,
                'preparation_time_minutes' => 25,
                'display_order' => 1,
            ]);
            $menuItem1->updateCostFromRecipe();

            $this->command->info("   ✓ Recipe-based: {$menuItem1->name} - \${$menuItem1->price} (Cost: \${$menuItem1->cost})");

            // Product-based menu items
            $menuItem2 = MenuItem::create([
                'store_id' => $store->id,
                'menu_category_id' => $drinkMenuCategory->id,
                'name' => 'Coca Cola',
                'description' => 'Refreshing cola drink',
                'price' => 3.00,
                'item_type' => 'product',
                'product_id' => $createdProducts['Coca Cola']->id,
                'is_active' => true,
                'is_available' => true,
                'display_order' => 1,
            ]);
            $menuItem2->updateCostFromProduct();

            $this->command->info("   ✓ Product-based: {$menuItem2->name} - \${$menuItem2->price} (Cost: \${$menuItem2->cost})");

            $menuItem3 = MenuItem::create([
                'store_id' => $store->id,
                'menu_category_id' => $drinkMenuCategory->id,
                'name' => 'Orange Juice',
                'description' => 'Fresh squeezed orange juice',
                'price' => 4.50,
                'item_type' => 'product',
                'product_id' => $createdProducts['Orange Juice']->id,
                'is_active' => true,
                'is_available' => true,
                'display_order' => 2,
            ]);
            $menuItem3->updateCostFromProduct();

            $this->command->info("   ✓ Product-based: {$menuItem3->name} - \${$menuItem3->price} (Cost: \${$menuItem3->cost})");

            // Simple menu item
            $menuItem4 = MenuItem::create([
                'store_id' => $store->id,
                'menu_category_id' => $mainCategory->id,
                'name' => 'Delivery Fee',
                'description' => 'Home delivery service',
                'price' => 5.00,
                'cost' => 2.00,
                'item_type' => 'simple',
                'is_active' => true,
                'is_available' => true,
                'display_order' => 99,
            ]);

            $this->command->info("   ✓ Simple item: {$menuItem4->name} - \${$menuItem4->price} (Cost: \${$menuItem4->cost})");

            DB::commit();

            $this->command->info('');
            $this->command->info('✅ Quick Start Setup Complete!');
            $this->command->info('');
            $this->command->info('═══════════════════════════════════════════');
            $this->command->info('📊 SUMMARY');
            $this->command->info('═══════════════════════════════════════════');
            $this->command->info('✓ Products Created: ' . count($createdProducts));
            $this->command->info('✓ Stock Added: Yes (all products have initial stock)');
            $this->command->info('✓ Recipes Created: 1 (Grilled Chicken Salad)');
            $this->command->info('✓ Menus Created: 1 (Lunch Menu)');
            $this->command->info('✓ Menu Categories: 2 (Main Courses, Beverages)');
            $this->command->info('✓ Menu Items: 4 (1 recipe-based, 2 product-based, 1 simple)');
            $this->command->info('');
            $this->command->info('🧪 TEST A SALE NOW:');
            $this->command->info('');
            $this->command->info('POST /api/restaurant-orders');
            $this->command->info('Content-Type: application/json');
            $this->command->info('X-Store-ID: ' . $store->id);
            $this->command->info('');
            $this->command->info('{');
            $this->command->info('  "menu_items": [');
            $this->command->info('    {"menu_item_id": ' . $menuItem1->id . ', "quantity": 2},');
            $this->command->info('    {"menu_item_id": ' . $menuItem2->id . ', "quantity": 1}');
            $this->command->info('  ],');
            $this->command->info('  "discount": 0,');
            $this->command->info('  "advance": 30.00');
            $this->command->info('}');
            $this->command->info('');
            $this->command->info('Expected: 2x Grilled Chicken Salad + 1x Coca Cola = $28.98');
            $this->command->info('');
            $this->command->info('═══════════════════════════════════════════');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Setup failed: ' . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }
}
