# Phase 2 Quick Reference Guide

**Menu Management System - Essential Commands & Usage**

---

## 🚀 Quick Start

### Check Migration Status
```bash
php artisan migrate:status
# Should show Phase 2 migrations as "Ran"
```

### Access via Tinker
```bash
php artisan tinker
```

---

## 📋 Common Operations

### 1. Create Menu Structure

#### Create a Menu
```php
use App\Services\Menu\MenuService;

$menuService = app(MenuService::class);

$menu = $menuService->createMenu([
    'name' => 'Lunch Menu',
    'description' => 'Available during lunch hours',
    'type' => 'lunch',
    'available_from_time' => '11:00:00',
    'available_to_time' => '15:00:00',
    'is_active' => true,
    'display_order' => 1,
    'store_id' => 1,
]);
```

#### Create a Category
```php
$category = $menuService->createCategory([
    'menu_id' => 1,
    'name' => 'Main Courses',
    'description' => 'Our signature dishes',
    'display_order' => 1,
    'is_active' => true,
]);
```

#### Create a Menu Item (Recipe-based)
```php
$menuItem = $menuService->createMenuItem([
    'menu_category_id' => 1,
    'name' => 'Classic Burger',
    'description' => 'Beef patty with lettuce, tomato, and special sauce',
    'price' => 12.99,
    'item_type' => 'recipe',
    'recipe_id' => 1, // Links to existing recipe
    'preparation_time_minutes' => 15,
    'is_active' => true,
    'is_available' => true,
    'store_id' => 1,
]);

// Cost is automatically calculated from recipe!
echo "Item cost: $" . $menuItem->cost;
echo "Profit margin: " . $menuItem->profit_margin_percentage . "%";
```

### 2. Query Operations

#### Get All Menus for Store
```php
$menus = $menuService->getMenusForStore($storeId = 1, $activeOnly = true);
```

#### Get Currently Available Menus (Time-based)
```php
$availableMenus = $menuService->getCurrentlyAvailableMenus($storeId = 1);
// Returns only menus available at current time
```

#### Get Menu with All Items
```php
$menu = $menuService->getMenuWithItems($menuId = 1, $activeOnly = true);

// Access structure
foreach ($menu->categories as $category) {
    echo "Category: {$category->name}\n";
    foreach ($category->items as $item) {
        echo "  - {$item->name}: ${item->price} (cost: ${item->cost})\n";
    }
}
```

### 3. Update Operations

#### Update Menu Item Price
```php
$menuItem = $menuService->updateMenuItem(1, [
    'price' => 14.99,
]);

// Profitability recalculates automatically
echo "New profit margin: " . $menuItem->profit_margin_percentage . "%";
```

#### Toggle Item Availability
```php
// Temporarily mark as unavailable (out of stock)
$menuItem = $menuService->toggleItemAvailability(1, false);

// Make available again
$menuItem = $menuService->toggleItemAvailability(1, true);
```

### 4. Profitability Analysis

#### Get Item Profitability
```php
$profitability = $menuService->calculateItemProfitability(1);

/*
Returns:
[
    'item_id' => 1,
    'item_name' => 'Classic Burger',
    'price' => 12.99,
    'cost' => 3.50,
    'profit_margin' => 9.49,
    'profit_margin_percentage' => 73.06,
    'food_cost_percentage' => 26.94,
    'recipe_linked' => true,
    'recipe_name' => 'Burger Recipe'
]
*/
```

#### Get Items Sorted by Profitability
```php
// By profit amount
$topItems = $menuService->getItemsByProfitability($storeId = 1, 'profit');

// By profit percentage
$topMargins = $menuService->getItemsByProfitability($storeId = 1, 'margin_percentage');

foreach ($topItems as $item) {
    echo "{$item->name}: ${$item->profit_margin} profit\n";
}
```

### 5. Statistics

#### Get Menu Statistics
```php
$stats = $menuService->getMenuStatistics($storeId = 1);

/*
Returns:
[
    'total_menus' => 4,
    'active_menus' => 3,
    'total_categories' => 12,
    'total_items' => 45,
    'active_items' => 42,
    'available_items' => 40,
    'recipe_based_items' => 38,
    'average_food_cost_percentage' => 28.5
]
*/
```

---

## 🔄 Automatic Cost Updates

### When Ingredient Price Changes
```php
// Change product price
use App\Services\PriceChange\PriceChangeService;

$priceService = app(PriceChangeService::class);
$logs = $priceService->applyBatch(
    productIds: [1], 
    prices: ['price_buy' => 5.00], 
    effectiveDate: now(), 
    reason: 'Supplier price increase'
);

// Automatically:
// 1. Recipe ingredients recalculate
// 2. Recipe total cost updates
// 3. Menu items linked to recipe update
// 4. Profitability metrics refresh
```

### Manual Cost Update for Menu Items
```php
// Force update menu item costs from recipe
$menuItem = \App\Models\MenuItem::find(1);
$menuItem->updateCostFromRecipe();

echo "Updated cost: $" . $menuItem->cost;
```

---

## 🎯 Model Direct Access

### Using Models Directly

```php
use App\Models\Menu;
use App\Models\MenuCategory;
use App\Models\MenuItem;

// Get active menus
$activeMenus = Menu::active()->get();

// Get menus available right now
$now = Menu::currentlyAvailable()->get();

// Get menu with categories and items
$menu = Menu::with(['categories.items.recipe'])->find(1);

// Get available items in a category
$category = MenuCategory::with('availableItems')->find(1);

// Check item profitability
$item = MenuItem::with('recipe')->find(1);
echo "Food Cost %: " . $item->food_cost_percentage;
echo "Profit: $" . $item->profit_margin;
```

### Query Scopes

```php
// Active menus for a store
$menus = Menu::active()->forStore(1)->get();

// Available menu items
$items = MenuItem::available()->forStore(1)->get();

// Recipe-based items only
$recipeItems = MenuItem::recipeBased()->get();
```

---

## 📡 API Endpoints

### Menus
```bash
# List all menus
GET /api/menus

# List currently available menus (time-based)
GET /api/menus/currently-available

# Get menu statistics
GET /api/menus/statistics

# Create menu
POST /api/menus
{
    "name": "Breakfast Menu",
    "type": "breakfast",
    "available_from_time": "07:00:00",
    "available_to_time": "11:00:00"
}

# Get menu with items
GET /api/menus/1?active_only=true

# Update menu
PUT /api/menus/1
{
    "name": "Updated Name",
    "is_active": false
}

# Delete menu
DELETE /api/menus/1
```

### Categories
```bash
# Create category
POST /api/menu-categories
{
    "menu_id": 1,
    "name": "Appetizers",
    "display_order": 1
}

# Update category
PUT /api/menu-categories/1
{
    "name": "Starters"
}

# Delete category
DELETE /api/menu-categories/1
```

### Menu Items
```bash
# Create item
POST /api/menu-items
{
    "menu_category_id": 1,
    "name": "Classic Burger",
    "price": 12.99,
    "item_type": "recipe",
    "recipe_id": 1
}

# Get item details
GET /api/menu-items/1

# Update item
PUT /api/menu-items/1
{
    "price": 14.99,
    "is_available": true
}

# Delete item
DELETE /api/menu-items/1

# Get profitability
GET /api/menu-items/1/profitability

# Toggle availability
POST /api/menu-items/1/toggle-availability
{
    "is_available": false
}

# Get items by profitability
GET /api/menu-items/by-profitability?order_by=margin_percentage
```

---

## 🔍 Common Queries

### Find Items with Low Profit Margins
```php
$lowMarginItems = MenuItem::where('store_id', 1)
    ->where('is_active', true)
    ->get()
    ->filter(fn($item) => $item->profit_margin_percentage < 30)
    ->sortBy('profit_margin_percentage');
```

### Find Items with High Food Cost
```php
$highFoodCost = MenuItem::where('store_id', 1)
    ->where('is_active', true)
    ->get()
    ->filter(fn($item) => $item->food_cost_percentage > 35);
```

### Get Menu Items Without Recipes
```php
$unlinkedItems = MenuItem::where('store_id', 1)
    ->where('item_type', 'recipe')
    ->whereNull('recipe_id')
    ->get();
```

### Check Time Availability
```php
$menu = Menu::find(1);
if ($menu->isCurrentlyAvailable()) {
    echo "Menu is available now!";
} else {
    echo "Menu not available at this time.";
}
```

---

## 🛠 Troubleshooting

### Menu Item Cost Not Updating
```php
// Force recalculation
$menuItem = MenuItem::find(1);
$menuItem->updateCostFromRecipe();

// Or recalculate all items for a recipe
$menuService->updateMenuItemCostsForRecipe($recipeId = 1);
```

### Check Which Items Use a Recipe
```php
$recipe = \App\Models\Recipe::find(1);
$menuItems = MenuItem::where('recipe_id', 1)->get();

foreach ($menuItems as $item) {
    echo "{$item->name} - Cost: ${$item->cost}\n";
}
```

### Verify Cost Cascade
```php
// Test cost update flow
$product = \App\Models\Product::find(1);
$oldPrice = $product->price_buy;

// Update price
$product->update(['price_buy' => $oldPrice + 1.00]);

// Check affected recipes
$ingredients = \App\Models\RecipeIngredient::where('product_id', 1)->get();
foreach ($ingredients as $ing) {
    echo "Recipe: {$ing->recipe->name} - New cost: {$ing->recipe->total_cost}\n";
    
    // Check menu items
    $items = MenuItem::where('recipe_id', $ing->recipe_id)->get();
    foreach ($items as $item) {
        echo "  Menu Item: {$item->name} - New cost: ${item->cost}\n";
    }
}
```

---

## 📊 Useful Reports

### Top 10 Most Profitable Items
```php
$topProfitable = MenuItem::where('store_id', 1)
    ->where('is_active', true)
    ->get()
    ->sortByDesc('profit_margin')
    ->take(10);
```

### Average Food Cost by Category
```php
$avgByCategory = DB::table('menu_items')
    ->join('menu_categories', 'menu_items.menu_category_id', '=', 'menu_categories.id')
    ->where('menu_items.store_id', 1)
    ->where('menu_items.is_active', true)
    ->where('menu_items.price', '>', 0)
    ->select('menu_categories.name', 
             DB::raw('AVG((menu_items.cost / menu_items.price) * 100) as avg_food_cost'))
    ->groupBy('menu_categories.name')
    ->get();
```

### Items Needing Price Adjustment
```php
// Items with food cost > 35% (target: <30%)
$needAdjustment = MenuItem::where('store_id', 1)
    ->where('is_active', true)
    ->get()
    ->filter(fn($item) => $item->food_cost_percentage > 35)
    ->map(function($item) {
        $targetPrice = $item->cost / 0.30; // Target 30% food cost
        return [
            'name' => $item->name,
            'current_price' => $item->price,
            'current_food_cost' => $item->food_cost_percentage,
            'suggested_price' => round($targetPrice, 2),
        ];
    });
```

---

## 🎓 Best Practices

### 1. Always Link Items to Recipes
```php
// ✅ Good: Automatic cost tracking
$item = $menuService->createMenuItem([
    'item_type' => 'recipe',
    'recipe_id' => 1,
    // ... other fields
]);

// ❌ Avoid: Manual cost entry (no automatic updates)
$item = $menuService->createMenuItem([
    'item_type' => 'simple',
    'cost' => 3.50, // Static, won't update
    // ... other fields
]);
```

### 2. Set Display Order
```php
// Ensure consistent ordering
$menuService->createMenu([
    'display_order' => 1, // Breakfast first
    // ...
]);

$menuService->createCategory([
    'display_order' => 1, // Appetizers first
    // ...
]);
```

### 3. Use Time Windows
```php
// Prevent breakfast items showing at dinner
$breakfastMenu = $menuService->createMenu([
    'type' => 'breakfast',
    'available_from_time' => '07:00:00',
    'available_to_time' => '11:00:00',
    // ...
]);
```

### 4. Monitor Food Cost
```php
// Regular checks
$stats = $menuService->getMenuStatistics(1);
if ($stats['average_food_cost_percentage'] > 32) {
    // Alert: Food cost too high
}
```

---

## 🔗 Related Documentation

- [Phase 1 Implementation](./PHASE_1_IMPLEMENTATION_COMPLETE.md) - Recipe & Costing System
- [Phase 2 Implementation](./PHASE_2_IMPLEMENTATION_COMPLETE.md) - Complete documentation
- [Roadmap](./RESTAURANT_ERP_IMPLEMENTATION_ROADMAP.md) - Overall project plan

---

**Last Updated:** May 19, 2026  
**Phase:** 2 - Menu Management ✅  
**Status:** Complete
