# Quick Test Seeder

## Overview

A minimal test seeder that creates exactly **one of everything** for rapid testing of the complete restaurant workflow.

## What Gets Created

1. **1 Menu**: "Test Lunch Menu" (11:00 AM - 3:00 PM)
2. **1 Category**: "Test Main Courses"
3. **1 Recipe**: "Test Grilled Chicken Salad" with 5 ingredients
4. **1 Menu Item**: Linked to the recipe ($12.99 price)

## Usage

### Quick Start

```bash
# Run test seeder only
php artisan db:seed --class=QuickTestSeeder

# Or run full database reset with test seeder
php artisan migrate:fresh --seed
```

### What You Get

```
Menu: Test Lunch Menu (ID: 1)
  └─ Category: Test Main Courses (ID: 1)
       └─ Menu Item: Test Grilled Chicken Salad (ID: 1)
            └─ Recipe: Test Grilled Chicken Salad (ID: 1)
                 ├─ Chicken Breast: 0.25 kg (10% waste)
                 ├─ Rice: 0.15 kg
                 ├─ Lettuce: 0.1 kg (15% waste)
                 ├─ Tomatoes: 0.05 kg (5% waste)
                 └─ Olive Oil: 0.02 L
```

### Cost Breakdown

- **Recipe Cost**: $2.77
- **Menu Item Price**: $12.99
- **Profit**: $10.22
- **Margin**: 78.6%
- **Food Cost %**: 21.4%

## Test Workflow

### 1. View Menu

```http
GET /api/menus
```

### 2. View Menu Items

```http
GET /api/menu-categories/1/menu-items
```

### 3. Preview Stock Deduction (Before Sale)

```http
POST /api/stock-deductions/preview
Content-Type: application/json

{
  "menu_item_id": 1,
  "quantity": 1
}
```

**Expected Response:**

```json
{
  "menu_item": "Test Grilled Chicken Salad",
  "quantity": 1,
  "recipe": "Test Grilled Chicken Salad",
  "ingredients": [
    {
      "product_name": "Chicken Breast",
      "quantity_needed": 0.275,
      "quantity_to_deduct": 0.275,
      "current_stock": 0,
      "remaining_stock": -0.275,
      "sufficient": false
    },
    ...
  ]
}
```

### 4. Add Stock First (Required!)

Before you can sell, you need stock. Create a purchase order:

```http
POST /api/order-purchases
Content-Type: application/json
X-Store-ID: 1

{
  "supplier_id": 1,
  "status": "completed",
  "reference": "PO-TEST-001",
  "products": [
    {
      "product_id": 1,
      "quantity": 50,
      "unit_price": 8.00
    },
    {
      "product_id": 2,
      "quantity": 100,
      "unit_price": 1.50
    },
    {
      "product_id": 3,
      "quantity": 20,
      "unit_price": 0.80
    },
    {
      "product_id": 4,
      "quantity": 30,
      "unit_price": 1.10
    },
    {
      "product_id": 5,
      "quantity": 10,
      "unit_price": 10.00
    }
  ]
}
```

### 5. Create Sale (Restaurant Order)

```http
POST /api/restaurant-orders
Content-Type: application/json
X-Store-ID: 1

{
  "menu_items": [
    {
      "menu_item_id": 1,
      "quantity": 2
    }
  ],
  "customer_id": null,
  "discount": 0,
  "advance": 26.00,
  "note": "Test order - Table 1"
}
```

**Expected Result:**

- ✅ Order created successfully
- ✅ Chicken stock: 50.00 → 49.45 kg
- ✅ Rice stock: 100.00 → 99.70 kg
- ✅ Lettuce stock: 20.00 → 19.77 kg
- ✅ Tomatoes stock: 30.00 → 29.895 kg
- ✅ Olive Oil stock: 10.00 → 9.96 L
- ✅ 5 stock movements created

### 6. Verify Stock Movements

```http
GET /api/stock-movements?type=sale&limit=10
```

**Expected Response:**

```json
[
  {
    "product_name": "Chicken Breast",
    "quantity": 0.55,
    "type": "sale",
    "direction": "out",
    "unit_cost": 8.00,
    "total_cost": 4.40
  },
  ...
]
```

### 7. Check Updated Stock

```http
GET /api/store-products?store_id=1
```

## Switching Between Test and Full Data

### Use Test Seeder (Current Setup)

In `database/seeders/DatabaseSeeder.php`:

```php
// Quick test seeder is active
$this->call(QuickTestSeeder::class);

// Full seeders are commented
// $this->call(MenuSeeder::class);
// $this->call(MenuCategorySeeder::class);
// $this->call(RecipeSeeder::class);
// $this->call(MenuItemSeeder::class);
```

### Use Full Seeders

In `database/seeders/DatabaseSeeder.php`:

```php
// Comment out test seeder
// $this->call(QuickTestSeeder::class);

// Uncomment full seeders
$this->call(MenuSeeder::class);
$this->call(MenuCategorySeeder::class);
$this->call(RecipeSeeder::class);
$this->call(MenuItemSeeder::class);
```

Then run:

```bash
php artisan migrate:fresh --seed
```

## Dependencies

The test seeder requires:

- ✅ Users (created in DatabaseSeeder)
- ✅ Stores (created in DatabaseSeeder)
- ✅ Units (from UnitsAndConversionsSeeder)
- ✅ Products (from QuickStartProductSeeder)
    - REF-001: Chicken Breast
    - REF-002: Rice
    - REF-003: Lettuce
    - REF-004: Tomatoes
    - REF-005: Olive Oil

## Troubleshooting

### "Products not found"

Run the product seeder first:

```bash
php artisan db:seed --class=QuickStartProductSeeder
```

### "Store or User not found"

Run the full seeder:

```bash
php artisan migrate:fresh --seed
```

### "Insufficient stock"

Create a purchase order first (see step 4 above).

## Re-running the Seeder

The seeder uses `updateOrCreate()`, so you can run it multiple times:

```bash
php artisan db:seed --class=QuickTestSeeder
```

It will update existing records if they already exist.

## Comparison: Test vs Full Seeders

| Feature    | QuickTestSeeder | Full Seeders                         |
| ---------- | --------------- | ------------------------------------ |
| Menus      | 1               | 4 (Breakfast, Lunch, Dinner, Drinks) |
| Categories | 1               | 12+                                  |
| Recipes    | 1               | 5                                    |
| Menu Items | 1               | 11+                                  |
| Setup Time | ~1 second       | ~3 seconds                           |
| Use Case   | Quick testing   | Demo/Production                      |

## Success Indicators

✅ **Test seeder worked if:**

- No errors during seeding
- Menu ID: 1 exists
- Category ID: 1 exists
- Recipe ID: 1 with 5 ingredients
- Menu Item ID: 1 with price $12.99
- Recipe cost: $2.77
- Food cost %: 21.4%

❌ **Something wrong if:**

- Recipe cost is $0.00
- Menu item cost ≠ $2.77
- No ingredients created
- Stock deduction fails

## Next Steps

1. ✅ Verify test data: `GET /api/menus`
2. ✅ Add stock: Purchase order for products
3. ✅ Test sale: Create restaurant order
4. ✅ Check stock movements
5. ✅ Verify cost calculations

## Clean Up

To remove test data and start fresh:

```bash
php artisan migrate:fresh --seed
```

This will drop all tables and recreate everything with the current seeder configuration.
