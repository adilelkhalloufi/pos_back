<?php

/**
 * Diagnostic script to check menu items configuration for stock deduction
 * 
 * Run with: php tests/check_menu_items_stock.php
 */

require __DIR__ . '/../vendor/autoload.php';

use App\Models\MenuItem;
use App\Models\StoreProducts;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=" . str_repeat("=", 80) . "\n";
echo "MENU ITEMS STOCK DEDUCTION DIAGNOSTIC\n";
echo "=" . str_repeat("=", 80) . "\n\n";

$menuItems = MenuItem::with(['product', 'recipe.ingredients.product'])
    ->where('is_active', true)
    ->get();

echo "Total Active Menu Items: " . $menuItems->count() . "\n\n";

foreach ($menuItems as $item) {
    echo str_repeat("-", 80) . "\n";
    echo "Menu Item: {$item->name} (ID: {$item->id})\n";
    echo "Type: {$item->item_type}\n";
    echo "Price: \${$item->price}\n";
    echo "Store ID: {$item->store_id}\n";

    // Check stock deduction configuration
    $hasStockDeduction = false;
    $issues = [];

    switch ($item->item_type) {
        case MenuItem::ITEM_TYPE_PRODUCT:
            echo "Stock Type: PRODUCT-BASED\n";

            if (!$item->product_id) {
                $issues[] = "❌ No product_id set!";
            } else {
                echo "Product ID: {$item->product_id}\n";

                if ($item->product) {
                    echo "Linked Product: {$item->product->name}\n";

                    // Check if StoreProduct exists
                    $storeProduct = StoreProducts::where('product_id', $item->product_id)
                        ->where('store_id', $item->store_id)
                        ->first();

                    if ($storeProduct) {
                        echo "✅ StoreProduct exists - Current stock: {$storeProduct->stock}\n";
                        $hasStockDeduction = true;
                    } else {
                        $issues[] = "❌ No StoreProduct record for this product in this store!";
                        $issues[] = "   Fix: Add product to store inventory via /api/store/{$item->store_id}/products";
                    }
                } else {
                    $issues[] = "❌ Product with ID {$item->product_id} not found!";
                }
            }
            break;

        case MenuItem::ITEM_TYPE_RECIPE:
            echo "Stock Type: RECIPE-BASED\n";

            if (!$item->recipe_id) {
                $issues[] = "❌ No recipe_id set!";
            } else {
                echo "Recipe ID: {$item->recipe_id}\n";

                if ($item->recipe) {
                    echo "Linked Recipe: {$item->recipe->name}\n";
                    $ingredients = $item->recipe->ingredients;
                    echo "Ingredients: " . $ingredients->count() . "\n";

                    if ($ingredients->count() > 0) {
                        $hasStockDeduction = true;
                        $missingStock = [];

                        foreach ($ingredients as $ingredient) {
                            $product = $ingredient->product;
                            $storeProduct = StoreProducts::where('product_id', $ingredient->product_id)
                                ->where('store_id', $item->store_id)
                                ->first();

                            if (!$storeProduct) {
                                $missingStock[] = $product->name;
                            }
                        }

                        if (!empty($missingStock)) {
                            $issues[] = "⚠️  Some ingredients not in store inventory: " . implode(', ', $missingStock);
                        } else {
                            echo "✅ All ingredients have StoreProduct records\n";
                        }
                    } else {
                        $issues[] = "❌ Recipe has no ingredients!";
                    }
                } else {
                    $issues[] = "❌ Recipe with ID {$item->recipe_id} not found!";
                }
            }
            break;

        case MenuItem::ITEM_TYPE_SIMPLE:
            echo "Stock Type: SIMPLE (No stock tracking)\n";
            echo "ℹ️  Simple items don't deduct stock (e.g., delivery fees, service charges)\n";
            break;

        case MenuItem::ITEM_TYPE_COMBO:
            echo "Stock Type: COMBO\n";
            $issues[] = "⚠️  Combo type not fully implemented for stock deduction";
            break;

        default:
            $issues[] = "❌ Unknown item_type: {$item->item_type}";
    }

    if ($hasStockDeduction) {
        echo "✅ Stock deduction: CONFIGURED\n";
    } elseif (empty($issues) && $item->item_type === MenuItem::ITEM_TYPE_SIMPLE) {
        echo "✅ No stock deduction needed (simple item)\n";
    } else {
        echo "❌ Stock deduction: NOT CONFIGURED\n";
    }

    if (!empty($issues)) {
        echo "\nISSUES FOUND:\n";
        foreach ($issues as $issue) {
            echo "  $issue\n";
        }
    }

    echo "\n";
}

echo str_repeat("=", 80) . "\n";
echo "SUMMARY\n";
echo str_repeat("=", 80) . "\n";

$productBased = $menuItems->where('item_type', MenuItem::ITEM_TYPE_PRODUCT)->count();
$recipeBased = $menuItems->where('item_type', MenuItem::ITEM_TYPE_RECIPE)->count();
$simple = $menuItems->where('item_type', MenuItem::ITEM_TYPE_SIMPLE)->count();
$combo = $menuItems->where('item_type', MenuItem::ITEM_TYPE_COMBO)->count();

echo "Product-based items: $productBased\n";
echo "Recipe-based items: $recipeBased\n";
echo "Simple items (no stock): $simple\n";
echo "Combo items: $combo\n";
echo "\nTotal: " . $menuItems->count() . "\n";

echo "\nRECOMMENDATIONS:\n";
echo "1. All product-based items must have:\n";
echo "   - product_id set\n";
echo "   - Corresponding StoreProducts record\n";
echo "2. All recipe-based items must have:\n";
echo "   - recipe_id set\n";
echo "   - Recipe with ingredients\n";
echo "   - All ingredients in StoreProducts\n";
echo "3. Test a sale to verify stock deduction now works!\n";
