<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\MenuItem;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "COMPLETE RECIPE & MENU ITEM COST UPDATE TEST\n";
echo str_repeat("=", 80) . "\n\n";

$menuItem = MenuItem::with(['recipe.ingredients.product.unit', 'recipe.ingredients.unit'])->first();

if (!$menuItem || !$menuItem->recipe) {
    echo "No recipe-based menu item found!\n";
    exit;
}

$recipe = $menuItem->recipe;

echo "STEP 1: Current State\n";
echo str_repeat("-", 80) . "\n";
echo "Menu Item: {$menuItem->name}\n";
echo "Menu Item Cost: \${$menuItem->cost}\n";
echo "Menu Item Price: \${$menuItem->price}\n";
echo "Recipe Total Cost: \${$recipe->total_cost}\n";
echo "Recipe Cost Per Serving: \${$recipe->cost_per_serving}\n\n";

echo "STEP 2: Recalculate Ingredient Costs (with unit conversion)\n";
echo str_repeat("-", 80) . "\n";

$totalIngredientCost = 0;
foreach ($recipe->ingredients as $ingredient) {
    $cost = $ingredient->calculateCost();
    echo "  {$ingredient->product->name}: \${$cost}\n";
    $totalIngredientCost += $cost;
}
echo "  TOTAL: \${$totalIngredientCost}\n\n";

echo "STEP 3: Recalculate Recipe Total Cost\n";
echo str_repeat("-", 80) . "\n";
$recipeTotalCost = $recipe->calculateTotalCost();
echo "  Recipe Total Cost: \${$recipeTotalCost}\n";
echo "  Yield Quantity: {$recipe->yield_quantity}\n";
echo "  Cost Per Serving: \${$recipe->fresh()->cost_per_serving}\n\n";

echo "STEP 4: Update Menu Item Cost from Recipe\n";
echo str_repeat("-", 80) . "\n";
$menuItem->updateCostFromRecipe();
$menuItem = $menuItem->fresh();
echo "  Menu Item Cost: \${$menuItem->cost}\n\n";

echo "STEP 5: Final Summary\n";
echo str_repeat("=", 80) . "\n";
echo sprintf("%-30s: $%10.2f\n", "Recipe Ingredient Cost", $recipeTotalCost);
echo sprintf("%-30s: $%10.2f\n", "Menu Item Cost", $menuItem->cost);
echo sprintf("%-30s: $%10.2f\n", "Menu Item Price", $menuItem->price);
echo str_repeat("-", 80) . "\n";
echo sprintf("%-30s: $%10.2f\n", "Profit", $menuItem->price - $menuItem->cost);
echo sprintf("%-30s: %9.2f%%\n", "Profit Margin", (($menuItem->price - $menuItem->cost) / $menuItem->price) * 100);
echo sprintf("%-30s: %9.2f%%\n", "Food Cost %", ($menuItem->cost / $menuItem->price) * 100);
echo str_repeat("=", 80) . "\n\n";

echo "✅ Cost calculation complete!\n";
echo "\nNow when you create or update recipes, costs will be calculated correctly\n";
echo "with proper unit conversion between grams/kilograms, milliliters/liters, etc.\n";
