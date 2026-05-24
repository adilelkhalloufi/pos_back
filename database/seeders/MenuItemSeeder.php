<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\Store;
use Illuminate\Database\Seeder;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::query()->first();

        if (!$store) {
            $this->command->error('Store not found. Please run StoreSeeds first.');
            return;
        }

        // Get menus and categories
        $breakfastMenu = Menu::query()->where('type', Menu::TYPE_BREAKFAST)->first();
        $lunchMenu = Menu::query()->where('type', Menu::TYPE_LUNCH)->first();
        $dinnerMenu = Menu::query()->where('type', Menu::TYPE_DINNER)->first();
        $drinksMenu = Menu::query()->where('type', Menu::TYPE_DRINKS)->first();

        // Get recipes
        $grilledChickenRice = Recipe::query()->where('name', 'Grilled Chicken with Rice')->first();
        $gardenSalad = Recipe::query()->where('name', 'Fresh Garden Salad')->first();
        $chickenSaladBowl = Recipe::query()->where('name', 'Chicken Salad Bowl')->first();
        $riceBowl = Recipe::query()->where('name', 'Simple Rice Bowl')->first();
        $herbChicken = Recipe::query()->where('name', 'Herb-Roasted Chicken')->first();

        // Get products for direct menu items (simple type)
        $oliveOil = Product::query()->where('reference', 'REF-005')->first();

        // Menu items structure
        $menuItems = [
            // Breakfast Menu Items
            [
                'menu' => $breakfastMenu,
                'category_name' => 'Plats principaux',
                'items' => [
                    [
                        'name' => 'Morning Chicken Bowl',
                        'description' => 'Start your day with protein-rich grilled chicken and rice',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $grilledChickenRice,
                        'price' => 9.50,
                        'display_order' => 1,
                    ],
                    [
                        'name' => 'Light Rice Bowl',
                        'description' => 'Healthy rice bowl with fresh vegetables',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $riceBowl,
                        'price' => 6.00,
                        'display_order' => 2,
                    ],
                ],
            ],
            [
                'menu' => $breakfastMenu,
                'category_name' => 'Entrées',
                'items' => [
                    [
                        'name' => 'Fresh Morning Salad',
                        'description' => 'Crisp garden salad to start your day fresh',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $gardenSalad,
                        'price' => 4.50,
                        'display_order' => 1,
                    ],
                ],
            ],

            // Lunch Menu Items
            [
                'menu' => $lunchMenu,
                'category_name' => 'Plats principaux',
                'items' => [
                    [
                        'name' => 'Grilled Chicken Plate',
                        'description' => 'Perfectly grilled chicken breast with rice',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $grilledChickenRice,
                        'price' => 12.00,
                        'display_order' => 1,
                    ],
                    [
                        'name' => 'Healthy Bowl',
                        'description' => 'Complete meal with chicken, rice and fresh vegetables',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $chickenSaladBowl,
                        'price' => 10.50,
                        'display_order' => 2,
                    ],
                    [
                        'name' => 'Herb-Roasted Chicken',
                        'description' => 'Tender roasted chicken with aromatic herbs',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $herbChicken,
                        'price' => 13.50,
                        'display_order' => 3,
                    ],
                ],
            ],
            [
                'menu' => $lunchMenu,
                'category_name' => 'Entrées',
                'items' => [
                    [
                        'name' => 'Garden Salad',
                        'description' => 'Fresh lettuce and tomatoes with olive oil dressing',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $gardenSalad,
                        'price' => 5.00,
                        'display_order' => 1,
                    ],
                ],
            ],

            // Dinner Menu Items
            [
                'menu' => $dinnerMenu,
                'category_name' => 'Plats principaux',
                'items' => [
                    [
                        'name' => 'Evening Chicken Special',
                        'description' => 'Our signature grilled chicken with fluffy rice',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $grilledChickenRice,
                        'price' => 14.00,
                        'display_order' => 1,
                    ],
                    [
                        'name' => 'Chef\'s Roasted Chicken',
                        'description' => 'Herb-marinated roasted chicken breast',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $herbChicken,
                        'price' => 15.00,
                        'display_order' => 2,
                    ],
                    [
                        'name' => 'Dinner Bowl',
                        'description' => 'Hearty bowl with grilled chicken and fresh salad',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $chickenSaladBowl,
                        'price' => 11.50,
                        'display_order' => 3,
                    ],
                ],
            ],
            [
                'menu' => $dinnerMenu,
                'category_name' => 'Entrées',
                'items' => [
                    [
                        'name' => 'Evening Garden Salad',
                        'description' => 'Light and refreshing salad to start your dinner',
                        'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                        'recipe' => $gardenSalad,
                        'price' => 5.50,
                        'display_order' => 1,
                    ],
                ],
            ],

            // Simple/Product-based items (if needed)
            [
                'menu' => $lunchMenu,
                'category_name' => 'Extras',
                'items' => [
                    [
                        'name' => 'Extra Olive Oil',
                        'description' => 'Premium extra virgin olive oil',
                        'item_type' => MenuItem::ITEM_TYPE_SIMPLE,
                        'product' => $oliveOil,
                        'price' => 1.00,
                        'display_order' => 1,
                    ],
                ],
            ],
        ];

        $createdCount = 0;

        foreach ($menuItems as $menuSection) {
            if (!$menuSection['menu']) {
                continue;
            }

            // Find or create category
            $category = MenuCategory::query()
                ->where('menu_id', $menuSection['menu']->id)
                ->where('name', $menuSection['category_name'])
                ->first();

            if (!$category) {
                $this->command->warn("Category '{$menuSection['category_name']}' not found for menu '{$menuSection['menu']->name}'. Skipping...");
                continue;
            }

            foreach ($menuSection['items'] as $itemData) {
                // Skip if recipe/product is missing for recipe/product type items
                if ($itemData['item_type'] === MenuItem::ITEM_TYPE_RECIPE && !isset($itemData['recipe'])) {
                    continue;
                }
                if (($itemData['item_type'] === MenuItem::ITEM_TYPE_SIMPLE || $itemData['item_type'] === MenuItem::ITEM_TYPE_PRODUCT)
                    && !isset($itemData['product'])
                ) {
                    continue;
                }

                // Calculate cost from recipe if available
                $cost = 0;
                $prepTime = null;
                $recipeId = null;
                $productId = null;

                if ($itemData['item_type'] === MenuItem::ITEM_TYPE_RECIPE && isset($itemData['recipe'])) {
                    $cost = $itemData['recipe']->cost_per_serving ?? 0;
                    $prepTime = $itemData['recipe']->preparation_time_minutes + $itemData['recipe']->cooking_time_minutes;
                    $recipeId = $itemData['recipe']->id;
                } elseif (isset($itemData['product'])) {
                    $cost = $itemData['product']->price_buy ?? 0;
                    $productId = $itemData['product']->id;
                }

                $menuItem = MenuItem::create([
                    'menu_category_id' => $category->id,
                    'name' => $itemData['name'],
                    'description' => $itemData['description'],
                    'image' => null,
                    'price' => $itemData['price'],
                    'cost' => $cost,
                    'is_active' => true,
                    'is_available' => true,
                    'preparation_time_minutes' => $prepTime,
                    'item_type' => $itemData['item_type'],
                    'recipe_id' => $recipeId,
                    'product_id' => $productId,
                    'store_id' => $store->id,
                    'display_order' => $itemData['display_order'],
                ]);

                $createdCount++;

                $profit = $itemData['price'] - $cost;
                $margin = $itemData['price'] > 0 ? ($profit / $itemData['price']) * 100 : 0;

                $this->command->info(
                    "Created: {$menuItem->name} | Price: {$itemData['price']} | Cost: {$cost} | Profit: {$profit} | Margin: " . number_format($margin, 1) . "%"
                );
            }
        }

        $this->command->info("Menu item seeding completed! Created {$createdCount} menu items.");
    }
}
