# Menu Item Product Implementation Guide

## Overview

**YES - MenuItem IS the sellable object for restaurant flow.**

The `OrderSaleObserver` confirms that when an order is created, it checks:

```php
if ($orderItem->product_type === MenuItem::class && $orderItem->product_id)
```

This means `MenuItem` is polymorphically linked to `OrderItems` and is the final sellable entity in the restaurant flow.

---

## What Was Implemented

### 1. Database Migration

**File:** `database/migrations/2026_05_23_000001_add_product_id_to_menu_items.php`

Added `product_id` column to `menu_items` table to support direct product linking.

```php
$table->foreignId('product_id')->nullable()->after('recipe_id')->constrained()->onDelete('set null');
$table->index('product_id');
```

**Run migration:**

```bash
php artisan migrate
```

---

### 2. MenuItem Model Updates

**File:** `app/Models/MenuItem.php`

#### Added:

- New constant: `COL_PRODUCT_ID = 'product_id'`
- New item type: `ITEM_TYPE_PRODUCT = 'product'`
- Added `product_id` to `$fillable` array
- New relationship: `product()` - links to Product model
- New method: `updateCostFromProduct()` - automatically updates cost from product's purchase price

#### Item Types Now Supported:

1. **`recipe`** - Menu item based on a recipe (multiple ingredients)
2. **`combo`** - Menu item that's a combination of other items
3. **`simple`** - Menu item with manual price/cost (no linking)
4. **`product`** - Menu item directly linked to a single product ✨ NEW

---

### 3. Controller Validation Updates

**File:** `app/Http/Controllers/api/MenuItemController.php`

#### Updated Validation Rules:

```php
'item_type' => 'required|in:recipe,combo,simple,product',
'product_id' => 'nullable|exists:products,id',
```

Both `store()` and `update()` methods now accept `product_id` and the new `product` item type.

#### Updated `show()` Method:

Now loads product relationship:

```php
MenuItem::with(['recipe', 'product', 'category', 'store'])->findOrFail($id);
```

---

### 4. MenuService Updates

**File:** `app/Services/Menu/MenuService.php`

#### `createMenuItem()` Method:

Now handles product-based items:

```php
if ($item->item_type === MenuItem::ITEM_TYPE_PRODUCT && $item->product_id) {
    $item->updateCostFromProduct();
}
```

#### `updateMenuItem()` Method:

Automatically recalculates cost when product is changed:

```php
elseif (isset($itemData['product_id']) && $item->item_type === MenuItem::ITEM_TYPE_PRODUCT) {
    $item->updateCostFromProduct();
}
```

---

### 5. Stock Deduction Service Updates

**File:** `app/Services/Stock/StockDeductionService.php`

#### Enhanced `deductMenuItemStock()` Method:

Now handles BOTH product-based and recipe-based menu items:

**For Product-Based Menu Items:**

- Deducts stock directly from the linked product
- Creates stock movement for the single product
- Checks stock availability before deduction

**For Recipe-Based Menu Items:**

- Deducts stock from all recipe ingredients (existing behavior)
- Handles unit conversions and waste percentages

```php
// Product-based deduction
if ($menuItem->item_type === MenuItem::ITEM_TYPE_PRODUCT && $menuItem->product_id) {
    // Deduct product stock directly
    $storeProduct->decrement('stock', $quantity);
}
```

---

### 6. MenuItemResource Updates

**File:** `app/Http/Resources/MenuItemResource.php`

Added `product_id` field and product relationship to API responses:

```php
MenuItem::COL_PRODUCT_ID => $this->product_id,
'product' => new ProductResource($this->whenLoaded('product')),
```

---

### 7. NEW Restaurant Sale Function

**File:** `app/Services/OrderSale/SaleService.php`

#### New Method: `createRestaurantOrder()`

**Purpose:** Create orders specifically for restaurant/menu-based sales (not direct product sales).

**Usage:**

```php
$orderData = [
    'menu_items' => [
        ['menu_item_id' => 1, 'quantity' => 2],
        ['menu_item_id' => 3, 'quantity' => 1],
    ],
    'customer_id' => 5, // optional
    'discount' => 10,   // optional
    'advance' => 50,    // optional
    'note' => 'Table 5' // optional
];

$sale = $saleService->createRestaurantOrder($orderData);
```

**What it does:**

1. Validates that menu items exist and are available
2. Calculates total from menu item prices
3. Creates OrderSale record
4. Creates OrderItems with `product_type = MenuItem::class`
5. Stock deduction happens automatically via `OrderSaleObserver`

---

## How to Use

### Creating a Menu Item with Direct Product Link

#### Step 1: Create a regular product

```http
POST /api/products
Content-Type: application/json
X-Store-ID: 1

{
  "name": "Coca Cola 330ml",
  "reference": "COKE-330",
  "category_id": 4,
  "unit_id": 2,
  "price": 2.50,
  "price_buy": 1.00,
  "stock_alert": 20,
  "is_stockable": true
}
```

#### Step 2: Create Menu Item linked to Product

```http
POST /api/menu-items
Content-Type: application/json
X-Store-ID: 1

{
  "menu_category_id": 5,
  "name": "Coca Cola",
  "description": "Refreshing soda",
  "price": 3.00,
  "item_type": "product",
  "product_id": 123,
  "is_active": true,
  "is_available": true
}
```

**Result:**

- Cost automatically set to `1.00` (product's purchase price)
- Food cost percentage: 33.3%
- Profit margin: $2.00

---

### Creating a Restaurant Order

```http
POST /api/restaurant-orders
Content-Type: application/json
X-Store-ID: 1

{
  "menu_items": [
    {
      "menu_item_id": 1,
      "quantity": 2
    },
    {
      "menu_item_id": 5,
      "quantity": 1
    }
  ],
  "customer_id": 10,
  "discount": 5.00,
  "advance": 20.00,
  "note": "Table 8 - No ice"
}
```

---

## Complete Flow Explanation

### Restaurant Order Flow (Menu Item → OrderItem → Stock Deduction)

```
1. User creates restaurant order with menu items
   ↓
2. SaleService.createRestaurantOrder() validates and creates OrderSale
   ↓
3. Creates OrderItems with:
   - product_type = MenuItem::class
   - product_id = menu_item_id
   ↓
4. OrderSaleObserver.created() fires automatically
   ↓
5. Checks: if (product_type === MenuItem::class)
   ↓
6. Calls StockDeductionService.deductMenuItemStock()
   ↓
7a. If item_type = 'product': Deducts single product stock
7b. If item_type = 'recipe': Deducts all recipe ingredients
   ↓
8. Stock movements created
   ↓
9. Order complete ✅
```

---

## Key Differences: MenuItem Types

| Type        | Link To        | Cost Source               | Stock Deduction                |
| ----------- | -------------- | ------------------------- | ------------------------------ |
| **recipe**  | Recipe ID      | Sum of recipe ingredients | Deducts all recipe ingredients |
| **product** | Product ID     | Product purchase price    | Deducts single product         |
| **simple**  | None           | Manual entry              | No automatic deduction         |
| **combo**   | Multiple items | Sum of items              | Depends on implementation      |

---

## API Endpoints Summary

### Menu Items

- `POST /api/menu-items` - Create menu item (with product_id support)
- `GET /api/menu-items/{id}` - Get menu item details
- `PUT /api/menu-items/{id}` - Update menu item
- `DELETE /api/menu-items/{id}` - Delete menu item
- `GET /api/menu-items/by-profitability` - Get items sorted by profit
- `POST /api/menu-items/{id}/toggle-availability` - Toggle availability

### Restaurant Orders

- `POST /api/restaurant-orders` - Create restaurant order (NEW)
- Uses existing order endpoints for viewing/updating

---

## Important Notes

### When to Use Each Approach

**Use Product-Based Menu Item When:**

- Selling single products (drinks, packaged items)
- Product is sold as-is without preparation
- You want simple 1:1 product-to-menu-item mapping

**Use Recipe-Based Menu Item When:**

- Dish requires multiple ingredients
- Need to track ingredient consumption
- Complex preparation with waste tracking

**Use Simple Menu Item When:**

- Service items (delivery fee, tips)
- Items without inventory tracking
- Manual cost management needed

### Stock Management

- **Product-based items**: Stock is deducted from the linked product
- **Recipe-based items**: Stock is deducted from each recipe ingredient
- **Simple items**: No automatic stock deduction

### Cost Calculation

- **Product-based**: Cost = Product's `price_buy`
- **Recipe-based**: Cost = Sum of all ingredient costs (with waste)
- **Simple**: Cost = Manual entry

---

## Testing Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Create a product-based menu item via API
- [ ] Verify cost is auto-calculated from product
- [ ] Create a restaurant order with the menu item
- [ ] Check that stock was deducted from the product
- [ ] Verify stock movement was created
- [ ] Check order appears in reports

---

## Migration Command

```bash
# Run the new migration
php artisan migrate

# If you need to rollback
php artisan migrate:rollback --step=1
```

---

## Conclusion

**MenuItem is confirmed as the sellable object for restaurant operations.**

You now have THREE ways to create menu items:

1. **Recipe-based** - Links to recipes with multiple ingredients
2. **Product-based** - Links directly to a single product ✨ NEW
3. **Simple** - Manual price/cost, no links

The `createRestaurantOrder()` function provides a clean, restaurant-specific way to create orders that automatically handles stock deduction based on the menu item type.
