<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuickTestSeeder extends Seeder
{
    /**
     * Quick test seeder for complete workflow testing
     * Creates: 1 Menu → 1 Category → 1 Recipe → 1 Menu Item
     */
    public function run(): void
    {
        $store = Store::query()->first();
        $user = User::query()->first();

        if (!$store || !$user) {
            $this->command->error('Store or User not found. Please run basic setup first.');
            return;
        }

        // Get units
        $kgUnit = Unit::query()->where('symbol', 'kg')->first();
        $pcUnit = Unit::query()->where('symbol', 'pc')->first();
        $lUnit = Unit::query()->where('symbol', 'L')->first();
        $gUnit = Unit::query()->where('symbol', 'g')->first();
        $mlUnit = Unit::query()->where('symbol', 'ml')->first();

        // Get products (assuming they exist from QuickStartProductSeeder)
        $chickenBreast = Product::query()->where('reference', 'REF-001')->first();
        $rice = Product::query()->where('reference', 'REF-002')->first();
        $lettuce = Product::query()->where('reference', 'REF-003')->first();
        $tomatoes = Product::query()->where('reference', 'REF-004')->first();
        $oliveOil = Product::query()->where('reference', 'REF-005')->first();

        if (!$chickenBreast || !$rice || !$lettuce || !$tomatoes || !$oliveOil) {
            $this->command->error('Products not found. Please run QuickStartProductSeeder first.');
            return;
        }

        $this->command->info('🚀 Starting Quick Test Seeder...');
        $this->command->line('');

        // ============================================
        // STEP 1: Create Menu
        // ============================================
        $this->command->info('📋 STEP 1: Creating Menu...');

        $menu = Menu::updateOrCreate(
            ['name' => 'Test Lunch Menu', 'store_id' => $store->id],
            [
                'description' => 'Test menu for quick workflow validation',
                'type' => Menu::TYPE_LUNCH,
                'available_from_time' => '11:00:00',
                'available_to_time' => '15:00:00',
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        $this->command->line("   ✓ Menu created: {$menu->name}");
        $this->command->line("   ✓ Time window: 11:00 AM - 3:00 PM");
        $this->command->line('');

        // ============================================
        // STEP 2: Create Menu Category
        // ============================================
        $this->command->info('📁 STEP 2: Creating Menu Category...');

        $category = MenuCategory::updateOrCreate(
            ['name' => 'Test Main Courses', 'menu_id' => $menu->id],
            [
                'description' => 'Test category for main dishes',
                'is_active' => true,
                'display_order' => 1,
            ]
        );

        $this->command->line("   ✓ Category created: {$category->name}");
        $this->command->line("   ✓ Under menu: {$menu->name}");
        $this->command->line('');

        // ============================================
        // STEP 3: Create Recipe
        // ============================================
        $this->command->info('🍳 STEP 3: Creating Recipe...');

        $recipe = Recipe::updateOrCreate(
            ['name' => 'Test Grilled Chicken Salad', 'store_id' => $store->id],
            [
                'description' => 'Test recipe: Grilled chicken with fresh vegetables',
                'instructions' => "1. Season chicken with salt and pepper\n2. Grill chicken for 6-8 minutes per side\n3. Prepare salad with lettuce and tomatoes\n4. Cook rice separately\n5. Combine all ingredients\n6. Drizzle with olive oil",
                'yield_quantity' => 1,
                'yield_unit_id' => $pcUnit?->id,
                'preparation_time_minutes' => 15,
                'cooking_time_minutes' => 10,
                'skill_level' => 'easy',
                'total_cost' => 0,
                'cost_per_serving' => 0,
                'version' => 1,
                'is_active' => true,
                'user_id' => $user->id,
            ]
        );

        $this->command->line("   ✓ Recipe created: {$recipe->name}");
        $this->command->line("   ✓ Yield: {$recipe->yield_quantity} serving(s)");
        $this->command->line("   ✓ Total time: " . ($recipe->preparation_time_minutes + $recipe->cooking_time_minutes) . " minutes");

        // Delete existing ingredients to avoid duplicates
        RecipeIngredient::where('recipe_id', $recipe->id)->delete();

        // ============================================
        // STEP 4: Add Recipe Ingredients
        // ============================================
        $this->command->info('🥗 STEP 4: Adding Recipe Ingredients...');

        $ingredients = [
            [
                'product' => $chickenBreast,
                'quantity' => 250,
                'unit' => $gUnit,
                'waste_percentage' => 10.00,
                'preparation_note' => 'Boneless, skinless - trim fat',
            ],
            [
                'product' => $rice,
                'quantity' => 150,
                'unit' => $gUnit,
                'waste_percentage' => 0,
                'preparation_note' => 'Cooked white rice',
            ],
            [
                'product' => $lettuce,
                'quantity' => 100,
                'unit' => $gUnit,
                'waste_percentage' => 15.00,
                'preparation_note' => 'Chopped fresh',
            ],
            [
                'product' => $tomatoes,
                'quantity' => 50,
                'unit' => $gUnit,
                'waste_percentage' => 5.00,
                'preparation_note' => 'Diced',
            ],
            [
                'product' => $oliveOil,
                'quantity' => 20,
                'unit' => $mlUnit,
                'waste_percentage' => 0,
                'preparation_note' => 'For dressing',
            ],
        ];

        $totalCost = 0;

        foreach ($ingredients as $ingredientData) {
            if (!$ingredientData['product'] || !$ingredientData['unit']) {
                continue;
            }

            // Calculate ingredient cost
            $productCost = $ingredientData['product']->price_buy ?? 0;
            $wasteMultiplier = 1 + ($ingredientData['waste_percentage'] / 100);
            $effectiveQuantity = $ingredientData['quantity'] * $wasteMultiplier;
            $ingredientCost = $productCost * $effectiveQuantity;
            $totalCost += $ingredientCost;

            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'product_id' => $ingredientData['product']->id,
                'quantity' => $ingredientData['quantity'],
                'unit_id' => $ingredientData['unit']->id,
                'waste_percentage' => $ingredientData['waste_percentage'],
                'preparation_note' => $ingredientData['preparation_note'],
                'is_optional' => false,
                'cost' => $ingredientCost,
            ]);

            $this->command->line("   ✓ Added: {$ingredientData['product']->name} ({$ingredientData['quantity']} {$ingredientData['unit']->symbol}) - Cost: " . number_format($ingredientCost, 2));
        }

        // Update recipe with calculated costs
        $costPerServing = $recipe->yield_quantity > 0
            ? $totalCost / $recipe->yield_quantity
            : $totalCost;

        $recipe->update([
            'total_cost' => $totalCost,
            'cost_per_serving' => $costPerServing,
        ]);

        $this->command->line('');
        $this->command->line("   💰 Total Recipe Cost: " . number_format($totalCost, 2));
        $this->command->line("   💰 Cost Per Serving: " . number_format($costPerServing, 2));
        $this->command->line('');

        // ============================================
        // STEP 5: Create Menu Item
        // ============================================
        $this->command->info('🍽️  STEP 5: Creating Menu Item...');

        $menuItemPrice = 12.99;

        $menuItem = MenuItem::updateOrCreate(
            ['name' => 'Test Grilled Chicken Salad', 'menu_category_id' => $category->id],
            [
                'description' => 'Test menu item: Fresh salad with grilled chicken breast',
                'image' => null,
                'price' => $menuItemPrice,
                'cost' => $costPerServing,
                'is_active' => true,
                'is_available' => true,
                'preparation_time_minutes' => $recipe->preparation_time_minutes + $recipe->cooking_time_minutes,
                'item_type' => MenuItem::ITEM_TYPE_RECIPE,
                'recipe_id' => $recipe->id,
                'product_id' => null,
                'store_id' => $store->id,
                'display_order' => 1,
            ]
        );

        $profit = $menuItemPrice - $costPerServing;
        $margin = $menuItemPrice > 0 ? ($profit / $menuItemPrice) * 100 : 0;

        $this->command->line("   ✓ Menu Item created: {$menuItem->name}");
        $this->command->line("   ✓ Category: {$category->name}");
        $this->command->line("   ✓ Price: $" . number_format($menuItemPrice, 2));
        $this->command->line("   ✓ Cost: $" . number_format($costPerServing, 2));
        $this->command->line("   ✓ Profit: $" . number_format($profit, 2));
        $this->command->line("   ✓ Margin: " . number_format($margin, 1) . "%");
        $this->command->line("   ✓ Food Cost %: " . number_format(($costPerServing / $menuItemPrice) * 100, 1) . "%");
        $this->command->line('');

        // ============================================
        // SUMMARY
        // ============================================
        $this->command->line('════════════════════════════════════════════════');
        $this->command->info('✅ QUICK TEST SEEDER COMPLETED!');
        $this->command->line('════════════════════════════════════════════════');
        $this->command->line('');
        $this->command->line('📊 Summary:');
        $this->command->line("   • Menu: {$menu->name} (ID: {$menu->id})");
        $this->command->line("   • Category: {$category->name} (ID: {$category->id})");
        $this->command->line("   • Recipe: {$recipe->name} (ID: {$recipe->id})");
        $this->command->line("   • Ingredients: " . count($ingredients));
        $this->command->line("   • Menu Item: {$menuItem->name} (ID: {$menuItem->id})");
        $this->command->line('');
        $this->command->line('🧪 Test this workflow:');
        $this->command->line('   1. View menu: GET /api/menus');
        $this->command->line("   2. View menu items: GET /api/menu-categories/{$category->id}/menu-items");
        $this->command->line("   3. Preview stock deduction: POST /api/stock-deductions/preview");
        $this->command->line('      Body: {"menu_item_id": ' . $menuItem->id . ', "quantity": 1}');
        $this->command->line('   4. Create sale: POST /api/restaurant-orders');
        $this->command->line('      Body: {"menu_items": [{"menu_item_id": ' . $menuItem->id . ', "quantity": 1}], "discount": 0, "advance": 15}');
        $this->command->line('   5. Check stock movements: GET /api/stock-movements');
        $this->command->line('');
        $this->command->info('✨ Ready for testing!');
        $this->command->line('');
    }
}
