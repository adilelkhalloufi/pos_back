<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\MenuItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Product;
use App\Models\StoreProducts;
use App\Models\Unit;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "RECIPE COST CALCULATION TEST (WITH UNIT CONVERSION)\n";
echo str_repeat("=", 100) . "\n\n";

$menuItem = MenuItem::with(['recipe.ingredients.product.unit', 'recipe.ingredients.unit'])->first();

if (!$menuItem || !$menuItem->recipe) {
    echo "No recipe-based menu item found!\n";
    exit;
}

$recipe = $menuItem->recipe;

echo "Menu Item: {$menuItem->name}\n";
echo "Recipe: {$recipe->name}\n";
echo "Current Recipe Cost: \${$recipe->cost}\n";
echo "Menu Item Sale Price: \${$menuItem->price}\n\n";

echo str_repeat("-", 100) . "\n";
echo sprintf(
    "%-25s | %10s | %12s | %12s | %10s | %10s | %12s\n",
    "Ingredient",
    "Qty",
    "Recipe Unit",
    "Product Unit",
    "Waste %",
    "Cost/Unit",
    "Total Cost"
);
echo str_repeat("-", 100) . "\n";

$totalCalculatedCost = 0;

foreach ($recipe->ingredients as $ingredient) {
    $product = $ingredient->product;
    $recipeUnit = $ingredient->unit;
    $productUnit = $product->unit;

    // Get cost per unit from StoreProducts
    $storeProduct = StoreProducts::where('product_id', $product->id)
        ->where('store_id', $recipe->store_id)
        ->first();

    $costPerUnit = $storeProduct ? $storeProduct->cost : ($product->price_buy ?? 0);

    // Show what conversion would happen
    $conversionNote = '';
    if ($ingredient->unit_id !== $product->unit_id) {
        $conversionNote = " ⚠️ NEEDS CONVERSION";
    } else {
        $conversionNote = " ✓ Same unit";
    }

    echo sprintf(
        "%-25s | %10.2f | %-12s | %-12s | %8.1f%% | $%9.4f | $%11.4f%s\n",
        substr($product->name, 0, 25),
        $ingredient->quantity,
        substr($recipeUnit->name ?? 'N/A', 0, 12),
        substr($productUnit->name ?? 'N/A', 0, 12),
        $ingredient->waste_percentage ?? 0,
        $costPerUnit,
        $ingredient->cost ?? 0,
        $conversionNote
    );

    $totalCalculatedCost += $ingredient->cost ?? 0;
}

echo str_repeat("-", 100) . "\n";
echo sprintf("%85s | $%11.4f\n", "TOTAL INGREDIENT COST", $totalCalculatedCost);
echo str_repeat("=", 100) . "\n\n";

// Now recalculate costs with the fixed method
echo "RECALCULATING COSTS WITH UNIT CONVERSION FIX...\n\n";

foreach ($recipe->ingredients as $ingredient) {
    $oldCost = $ingredient->cost;
    $newCost = $ingredient->calculateCost();

    if (abs($oldCost - $newCost) > 0.001) {
        echo sprintf(
            "%-30s: Old = $%-10.4f → New = $%-10.4f %s\n",
            substr($ingredient->product->name, 0, 30),
            $oldCost,
            $newCost,
            $newCost < $oldCost ? "✓ FIXED (decreased)" : "⚠️ (increased)"
        );
    } else {
        echo sprintf(
            "%-30s: $%-10.4f (no change)\n",
            substr($ingredient->product->name, 0, 30),
            $newCost
        );
    }
}

// Recalculate recipe total
$recipe->calculateTotalCost();
$recipe = $recipe->fresh();

echo "\n" . str_repeat("=", 100) . "\n";
echo "UPDATED RECIPE COST: \${$recipe->cost}\n";
echo "Menu Item Price: \${$menuItem->price}\n";
echo "Profit Margin: \$" . ($menuItem->price - $recipe->cost) . "\n";
echo "Profit %: " . (($menuItem->price - $recipe->cost) / $menuItem->price * 100) . "%\n";
echo str_repeat("=", 100) . "\n\n";

// Show example of unit conversion issue
echo "EXAMPLE OF THE BUG:\n";
echo str_repeat("-", 100) . "\n";
echo "If Chicken Breast costs \$10/Kilogram and recipe needs 250 Grams:\n\n";
echo "BEFORE FIX (Wrong):\n";
echo "  Effective Qty = 250g + 10% waste = 275g\n";
echo "  Cost = 275 × \$10 = \$2,750 ❌ WRONG! (treating grams as kilograms)\n\n";
echo "AFTER FIX (Correct):\n";
echo "  Effective Qty = 250g + 10% waste = 275g\n";
echo "  Convert to kg = 275g ÷ 1000 = 0.275kg\n";
echo "  Cost = 0.275kg × \$10/kg = \$2.75 ✓ CORRECT!\n";
echo str_repeat("-", 100) . "\n";
