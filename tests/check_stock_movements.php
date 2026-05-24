<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\MenuItem;
use App\Models\StoreProducts;
use App\Models\StockMovement;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "STOCK MOVEMENTS ANALYSIS\n";
echo str_repeat("=", 80) . "\n\n";

// Get the menu item
$menuItem = MenuItem::with(['recipe.ingredients.product'])->first();

if (!$menuItem) {
    echo "No menu items found!\n";
    exit;
}

echo "Menu Item: {$menuItem->name}\n";
echo "Type: {$menuItem->item_type}\n\n";

if ($menuItem->item_type === 'recipe' && $menuItem->recipe) {
    echo "Recipe: {$menuItem->recipe->name}\n";
    echo "Ingredients:\n";
    echo str_repeat("-", 80) . "\n";

    foreach ($menuItem->recipe->ingredients as $ingredient) {
        $product = $ingredient->product;
        $storeProduct = StoreProducts::where('product_id', $product->id)
            ->where('store_id', $menuItem->store_id)
            ->first();

        echo sprintf(
            "%-30s | Qty: %8.2f %s | Stock: %8.2f | Waste: %s%%\n",
            substr($product->name, 0, 30),
            $ingredient->quantity,
            $ingredient->unit ? $ingredient->unit->name : 'unit',
            $storeProduct ? $storeProduct->stock : 0,
            $ingredient->waste_percentage ?? 0
        );
    }

    echo "\n" . str_repeat("=", 80) . "\n";
    echo "RECENT STOCK MOVEMENTS (Last 10):\n";
    echo str_repeat("=", 80) . "\n";

    $movements = StockMovement::with('product')
        ->where('store_id', $menuItem->store_id)
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    if ($movements->count() > 0) {
        foreach ($movements as $movement) {
            echo sprintf(
                "%s | %-25s | %6s | %8.2f | %s\n",
                $movement->created_at->format('Y-m-d H:i'),
                substr($movement->product->name ?? 'Unknown', 0, 25),
                $movement->direction,
                $movement->quantity,
                $movement->note ?? ''
            );
        }
    } else {
        echo "No stock movements found!\n";
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "CURRENT STOCK LEVELS:\n";
echo str_repeat("=", 80) . "\n";

$storeProducts = StoreProducts::with('product')
    ->where('store_id', $menuItem->store_id)
    ->get();

foreach ($storeProducts as $sp) {
    echo sprintf(
        "%-40s | Stock: %10.2f\n",
        $sp->product->name ?? 'Unknown',
        $sp->stock
    );
}
