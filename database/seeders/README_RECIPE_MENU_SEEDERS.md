# Recipe and Menu Item Seeders

## Overview

Two new seeders have been created to populate the database with test recipes and menu items for the restaurant ERP system.

## Seeders Created

### 1. RecipeSeeder

**Location:** `database/seeders/RecipeSeeder.php`

**Purpose:** Creates sample recipes with their ingredients and automatically calculates costs.

**Recipes Created:**

- **Grilled Chicken with Rice** - Easy, 2 servings, 45 min total
- **Fresh Garden Salad** - Easy, 4 servings, 10 min total
- **Chicken Salad Bowl** - Medium, 1 serving, 45 min total
- **Simple Rice Bowl** - Easy, 2 servings, 30 min total
- **Herb-Roasted Chicken** - Medium, 3 servings, 50 min total

**Features:**

- Links recipes to existing products (Chicken, Rice, Lettuce, Tomatoes, Olive Oil)
- Automatically calculates recipe total cost and cost per serving
- Includes preparation notes and waste percentages
- Sets cooking times, skill levels, and instructions

**Dependencies:**

- Store must exist
- User must exist
- Products (REF-001 to REF-005) must exist
- Units must exist (kg, g, ml, L, pc)

### 2. MenuItemSeeder

**Location:** `database/seeders/MenuItemSeeder.php`

**Purpose:** Creates menu items and links them to recipes across different menus and meal periods.

**Menu Items Created:**

- **Breakfast Menu:** 3 items (Morning Chicken Bowl, Light Rice Bowl, Fresh Morning Salad)
- **Lunch Menu:** 5 items (Grilled Chicken Plate, Healthy Bowl, Herb-Roasted Chicken, Garden Salad, Extra Olive Oil)
- **Dinner Menu:** 4 items (Evening Chicken Special, Chef's Roasted Chicken, Dinner Bowl, Evening Garden Salad)

**Features:**

- Links menu items to recipes (recipe-based items)
- Links menu items to products (simple items)
- Automatically copies recipe costs to menu items
- Calculates profit margins
- Sets prices and display order
- Distributes items across different menu categories

**Dependencies:**

- Menus must exist (Breakfast, Lunch, Dinner)
- Menu categories must exist
- Recipes must exist (from RecipeSeeder)
- Store must exist

## How to Run

### Option 1: Run All Seeders

This will run the complete database seeding including recipes and menu items:

```bash
php artisan db:seed
```

### Option 2: Run Individual Seeders

To run only the recipe seeder:

```bash
php artisan db:seed --class=RecipeSeeder
```

To run only the menu item seeder:

```bash
php artisan db:seed --class=MenuItemSeeder
```

### Option 3: Run Both Together

```bash
php artisan db:seed --class=RecipeSeeder
php artisan db:seed --class=MenuItemSeeder
```

## Recommended Seeding Order

If running seeders individually, follow this order:

1. `PlansSeeder`
2. `RoleSeeder`
3. Create users and stores (in DatabaseSeeder)
4. `UnitsAndConversionsSeeder`
5. `SettingsSeeder`
6. `ModePayemntSeeder`
7. `CategorySeeds`
8. `QuickStartProductSeeder` ← Must run before recipes
9. `MenuSeeder`
10. `MenuCategorySeeder`
11. **`RecipeSeeder`** ← New
12. **`MenuItemSeeder`** ← New

## What Data Gets Created

### Recipes Table

- 5 recipes with complete details
- Automatic cost calculations
- Version tracking (all set to v1)
- Active status (all active)

### Recipe Ingredients Table

- Multiple ingredients per recipe
- Quantity and unit specifications
- Waste percentage tracking
- Preparation notes
- Individual ingredient cost calculations

### Menu Items Table

- 12 menu items total
- Linked to recipes or products
- Pricing with calculated profit margins
- Display order for UI
- Availability flags

## Cost Analysis Example

For **Grilled Chicken with Rice**:

- Chicken Breast: 0.4 kg × 8.00 = 3.20
- Rice: 0.2 kg × 1.50 = 0.30
- Olive Oil: 0.02 L × 10.00 = 0.20
- **Total Cost:** 3.70
- **Cost per Serving:** 1.85 (for 2 servings)

Menu items using this recipe can be priced with appropriate margins:

- Breakfast: 9.50 (margin ~80%)
- Lunch: 12.00 (margin ~85%)
- Dinner: 14.00 (margin ~87%)

## Testing the Data

After seeding, you can test with:

```php
// Get all recipes
$recipes = App\Models\Recipe::with('ingredients')->get();

// Get menu items with recipes
$menuItems = App\Models\MenuItem::with('recipe', 'menuCategory.menu')->get();

// Check cost calculations
$recipe = App\Models\Recipe::find(1);
echo "Total Cost: {$recipe->total_cost}";
echo "Cost Per Serving: {$recipe->cost_per_serving}";
```

## Notes

- All recipes use products from the QuickStartProductSeeder
- Costs are calculated automatically based on product purchase prices
- Menu items inherit preparation times from recipes
- The system supports three item types: recipe, simple, and combo
- Waste percentages are applied to ingredients (e.g., 15% for lettuce trimming)

## Troubleshooting

**Error: "Store or User not found"**

- Run: `php artisan db:seed` (full seeding) instead of individual seeders
- Or ensure users and stores are created first

**Error: "Product not found"**

- Make sure `QuickStartProductSeeder` has run
- Check that products with references REF-001 to REF-005 exist

**Error: "Menu or Category not found"**

- Run `MenuSeeder` and `MenuCategorySeeder` first

**No cost calculations showing**

- Verify products have `price_buy` values set
- Check that recipe ingredients were created successfully
