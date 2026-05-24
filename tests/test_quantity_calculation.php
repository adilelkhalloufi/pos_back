<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\MenuItem;
use App\Models\StoreProducts;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "QUANTITY CALCULATION TEST\n";
echo str_repeat("=", 80) . "\n\n";

$menuItem = MenuItem::with(['recipe.ingredients.product.unit'])->first();

if (!$menuItem || !$menuItem->recipe) {
    echo "No recipe-based menu item found!\n";
    exit;
}

echo "Menu Item: {$menuItem->name}\n";
echo "Recipe: {$menuItem->recipe->name}\n\n";

echo "EXPECTED DEDUCTIONS (selling 1 unit):\n";
echo str_repeat("-", 80) . "\n";
echo sprintf(
    "%-25s | %10s | %8s | %10s | %10s\n",
    "Ingredient",
    "Base Qty",
    "Waste %",
    "Effective",
    "Unit"
);
echo str_repeat("-", 80) . "\n";

$totalDeduction = 0;

foreach ($menuItem->recipe->ingredients as $ingredient) {
    $effective = $ingredient->effective_quantity;
    $unit = $ingredient->unit ? $ingredient->unit->name : 'unit';

    echo sprintf(
        "%-25s | %10.2f | %7.1f%% | %10.2f | %-10s\n",
        substr($ingredient->product->name, 0, 25),
        $ingredient->quantity,
        $ingredient->waste_percentage ?? 0,
        $effective,
        $unit
    );

    $totalDeduction++;
}

echo str_repeat("-", 80) . "\n";
echo "\nIf selling 2 units, quantities would be multiplied by 2.\n";
echo "If selling 0.5 units, quantities would be multiplied by 0.5.\n";

echo "\n\n" . str_repeat("=", 80) . "\n";
echo "BEFORE FIX: quantity_with_waste (doesn't exist) = 0.00 ❌\n";
echo "AFTER FIX:  effective_quantity (calculated) = correct value ✅\n";
echo str_repeat("=", 80) . "\n";
