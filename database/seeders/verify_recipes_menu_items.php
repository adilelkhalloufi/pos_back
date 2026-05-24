<?php

/**
 * Quick Verification Script for Recipes and Menu Items
 * 
 * Run with: php artisan tinker
 * Then paste and execute each section
 */

// ============================================
// 1. View All Recipes with Ingredients
// ============================================
echo "\n=== RECIPES ===\n";
$recipes = App\Models\Recipe::with('ingredients.product', 'ingredients.unit')->get();
foreach ($recipes as $recipe) {
    echo "\n{$recipe->name}\n";
    echo "  Description: {$recipe->description}\n";
    echo "  Yield: {$recipe->yield_quantity} servings\n";
    echo "  Prep Time: {$recipe->preparation_time_minutes} min | Cook Time: {$recipe->cooking_time_minutes} min\n";
    echo "  Total Cost: " . number_format($recipe->total_cost, 2) . " | Per Serving: " . number_format($recipe->cost_per_serving, 2) . "\n";
    echo "  Ingredients:\n";
    foreach ($recipe->ingredients as $ingredient) {
        echo "    - {$ingredient->product->name}: {$ingredient->quantity} {$ingredient->unit->symbol}";
        echo " (Cost: " . number_format($ingredient->cost, 2) . ")";
        if ($ingredient->waste_percentage > 0) {
            echo " [Waste: {$ingredient->waste_percentage}%]";
        }
        echo "\n";
    }
}

// ============================================
// 2. View All Menu Items with Recipes
// ============================================
echo "\n\n=== MENU ITEMS ===\n";
$menuItems = App\Models\MenuItem::with('recipe', 'menuCategory.menu')->get();
foreach ($menuItems as $item) {
    echo "\n{$item->name}\n";
    echo "  Menu: {$item->menuCategory->menu->name} > {$item->menuCategory->name}\n";
    echo "  Type: {$item->item_type}\n";
    if ($item->recipe) {
        echo "  Recipe: {$item->recipe->name}\n";
    }
    echo "  Price: " . number_format($item->price, 2) . " | Cost: " . number_format($item->cost, 2);
    $profit = $item->price - $item->cost;
    $margin = $item->price > 0 ? ($profit / $item->price) * 100 : 0;
    echo " | Profit: " . number_format($profit, 2) . " (" . number_format($margin, 1) . "%)\n";
}

// ============================================
// 3. Cost Analysis by Menu
// ============================================
echo "\n\n=== COST ANALYSIS BY MENU ===\n";
$menus = App\Models\Menu::with('categories.menuItems')->get();
foreach ($menus as $menu) {
    $items = $menu->categories->flatMap->menuItems;
    if ($items->count() > 0) {
        echo "\n{$menu->name}:\n";
        echo "  Total Items: {$items->count()}\n";
        echo "  Avg Price: " . number_format($items->avg('price'), 2) . "\n";
        echo "  Avg Cost: " . number_format($items->avg('cost'), 2) . "\n";
        echo "  Avg Margin: " . number_format($items->avg(function ($item) {
            return $item->price > 0 ? (($item->price - $item->cost) / $item->price) * 100 : 0;
        }), 1) . "%\n";
    }
}

// ============================================
// 4. Most Profitable Items
// ============================================
echo "\n\n=== TOP 5 MOST PROFITABLE ITEMS ===\n";
$topProfitable = App\Models\MenuItem::all()
    ->map(function ($item) {
        return [
            'name' => $item->name,
            'profit' => $item->price - $item->cost,
            'margin' => $item->price > 0 ? (($item->price - $item->cost) / $item->price) * 100 : 0,
        ];
    })
    ->sortByDesc('profit')
    ->take(5);

foreach ($topProfitable as $item) {
    echo "  {$item['name']}: " . number_format($item['profit'], 2) . " (" . number_format($item['margin'], 1) . "%)\n";
}

// ============================================
// 5. Recipe Ingredient Summary
// ============================================
echo "\n\n=== RECIPE INGREDIENTS SUMMARY ===\n";
$ingredients = App\Models\RecipeIngredient::with('product')
    ->selectRaw('product_id, SUM(quantity) as total_qty, COUNT(*) as recipe_count')
    ->groupBy('product_id')
    ->get();

foreach ($ingredients as $ingredient) {
    echo "  {$ingredient->product->name}: Used in {$ingredient->recipe_count} recipes\n";
}

echo "\n\n=== Verification Complete ===\n";
