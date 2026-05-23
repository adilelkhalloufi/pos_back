# Quick Start Checklist ✅

## From Product to Sale - Quick Reference

**Date:** May 23, 2026  
**Latest Update:** Added product-based menu items support

---

## 🚀 Quick Setup

**Before starting, run the latest migration:**

```bash
php artisan migrate
```

This adds support for direct product linking to menu items.

---

## 📋 9-Step Process

### ☐ STEP 1: Create Products

```http
POST /api/products
Content-Type: application/json

{
  "name": "Chicken Breast",
  "reference": "REF-001",
  "description": "Fresh chicken breast for recipes",
  "category_id": 1,
  "unit_id": 6,
  "price": 12.00,
  "price_buy": 8.00,
  "price_sell_1": 12.00,
  "stock_alert": 10,
  "is_active": true,
  "archive": false,
  "codebar": "1234567890123",
  "barcodes": ["1234567890123", "9876543210987"]
}
```

**Create:** Chicken Breast, Rice, Lettuce, Tomatoes, Olive Oil

**Example payloads for the remaining products:**

```http
POST /api/products
Content-Type: application/json

{
  "name": "Rice",
  "reference": "REF-002",
  "description": "Long-grain white rice",
  "category_id": 1,
  "unit_id": 1,
  "price": 2.50,
  "price_buy": 1.50,
  "price_sell_1": 2.50,
  "stock_alert": 20,
  "is_active": true,
  "archive": false,
  "codebar": "2234567890123",
  "barcodes": ["2234567890123"]
}
```

```http
POST /api/products
Content-Type: application/json

{
  "name": "Lettuce",
  "reference": "REF-003",
  "description": "Fresh lettuce for salads",
  "category_id": 2,
  "unit_id": 2,
  "price": 1.20,
  "price_buy": 0.80,
  "price_sell_1": 1.20,
  "stock_alert": 15,
  "is_active": true,
  "archive": false,
  "codebar": "3234567890123",
  "barcodes": ["3234567890123"]
}
```

```http
POST /api/products
Content-Type: application/json

{
  "name": "Tomatoes",
  "reference": "REF-004",
  "description": "Ripe tomatoes for cooking",
  "category_id": 2,
  "unit_id": 2,
  "price": 1.80,
  "price_buy": 1.10,
  "price_sell_1": 1.80,
  "stock_alert": 15,
  "is_active": true,
  "archive": false,
  "codebar": "4234567890123",
  "barcodes": ["4234567890123"]
}
```

```http
POST /api/products
Content-Type: application/json

{
  "name": "Olive Oil",
  "reference": "REF-005",
  "description": "Extra virgin olive oil",
  "category_id": 3,
  "unit_id": 3,
  "price": 15.00,
  "price_buy": 10.00,
  "price_sell_1": 15.00,
  "stock_alert": 5,
  "is_active": true,
  "archive": false,
  "codebar": "5234567890123",
  "barcodes": ["5234567890123"]
}
```

**Test:** ✓ Products appear in list

**Completion details:**

- Answer with the exact product fields you sent, including `name`, `reference`, `category_id`, `unit_id`, `price`, `price_buy`, and `stock_alert`.
- Verify each created product is visible in `GET /api/products`.
- Confirm the returned product has:
    - `id` assigned
    - correct `reference`
    - correct `category_id` and `unit_id`
    - `price`, `price_buy`, and `price_sell_1`
    - `barcodes` or `codebar` preserved if provided
- If the product list shows your new items, the step is complete.

---

### ☐ STEP 2: Setup Unit Conversions

```bash
php artisan db:seed --class=UnitsAndConversionsSeeder
```

**Creates:**

- 27 Units (kg, g, L, ml, piece, dozen, etc.)
- 70+ Conversions (KG↔Gram, Liter↔ML, etc.)

**Test:** ✓ Units table has 27 records, Conversions table has 70+ records

---

### ☐ STEP 3: Purchase Products (Add Stock)

```http
POST /api/order-purchases
```

**Example:** 50 KG Chicken @ $10/KG

**Test:** ✓ Stock = 50 KG, Cost = $10.00

---

### ☐ STEP 4: Create Recipe

**How to add a recipe:**

- Use `POST /api/recipes`
- Include `store_id` or send `X-Store-ID: 1`
- Add recipe ingredients in the `ingredients` array
- Each ingredient must include `product_id`, `quantity`, `unit_id`, and optional `waste_percentage`

```http
POST /api/recipes
Content-Type: application/json
X-Store-ID: 1

{
  "name": "Grilled Chicken Salad",
  "description": "Healthy grilled chicken with fresh vegetables",
  "instructions": "1. Season and grill chicken\n2. Prepare salad base\n3. Combine and dress",
  "yield_quantity": 1,
  "yield_unit_id": 2,
  "preparation_time_minutes": 15,
  "cooking_time_minutes": 10,
  "skill_level": "intermediate",
  "is_active": true,
  "store_id": 1,
  "ingredients": [
    {
      "product_id": 1,
      "quantity": 0.25,
      "unit_id": 1,
      "waste_percentage": 10,
      "preparation_note": "Boneless, skinless"
    },
    {
      "product_id": 2,
      "quantity": 0.15,
      "unit_id": 1,
      "waste_percentage": 0,
      "preparation_note": "Cooked rice"
    },
    {
      "product_id": 3,
      "quantity": 0.1,
      "unit_id": 1,
      "waste_percentage": 15,
      "preparation_note": "Chopped"
    },
    {
      "product_id": 4,
      "quantity": 0.05,
      "unit_id": 1,
      "waste_percentage": 5,
      "preparation_note": "Diced"
    },
    {
      "product_id": 5,
      "quantity": 0.02,
      "unit_id": 3,
      "waste_percentage": 0,
      "preparation_note": "For dressing"
    }
  ]
}
```

**Recipe example:** "Grilled Chicken Salad"

- 250g Chicken (10% waste)
- 150g Rice
- 100g Lettuce (15% waste)
- 50g Tomatoes (5% waste)
- 20ml Olive Oil

**Test:** ✓ Total cost = $3.98 calculated automatically

**Important Notes:**

- Create Recipe BEFORE Menu Item (recipe must exist to link)
- **NEW:** You can also link menu items directly to products (no recipe needed)
- `product_id` refers to products created in Step 1
- `unit_id` refers to units from the seeder (1=KG, 2=piece, 3=liter, etc.)
- `yield_unit_id` is the unit for the final recipe output
- Costs are calculated automatically based on current product prices and waste percentages
- The effective quantity includes waste: quantity × (1 + waste_percentage/100)
- **NEW:** Use `item_type='product'` for direct product sales (drinks, packaged items)

**Additional Recipe Endpoints:**

```http
# Get all recipes for a store
GET /api/recipes?active_only=true
X-Store-ID: 1

# Get specific recipe with cost breakdown
GET /api/recipes/{id}

# Update recipe
PUT /api/recipes/{id}
Content-Type: application/json
{
  "name": "Updated Recipe Name",
  "is_active": false
}

# Recalculate recipe cost (after product price changes)
POST /api/recipes/{id}/recalculate-cost

# Add an ingredient to an existing recipe
POST /api/recipes/{id}/ingredients
Content-Type: application/json
{
  "product_id": 2,
  "quantity": 0.10,
  "unit_id": 1,
  "waste_percentage": 0,
  "preparation_note": "Extra rice"
}

# Recalculate all recipe costs
POST /api/recipes-recalculate-all
```

POST /api/recipes/{id}/recalculate-cost

# Calculate profitability with a selling price

POST /api/recipes/{id}/profitability
Content-Type: application/json
{
"selling_price": 12.99
}

# Clone a recipe

POST /api/recipes/{id}/clone
Content-Type: application/json
{
"name": "Grilled Chicken Salad V2"
}

# Add ingredient to existing recipe

POST /api/recipes/{id}/ingredients
Content-Type: application/json
{
"product_id": 6,
"quantity": 0.05,
"unit_id": 1,
"waste_percentage": 5,
"preparation_note": "Optional topping"
}

# Update ingredient

PUT /api/recipes/{recipeId}/ingredients/{ingredientId}
Content-Type: application/json
{
"quantity": 0.3,
"waste_percentage": 12
}

# Remove ingredient

DELETE /api/recipes/{recipeId}/ingredients/{ingredientId}

# Recalculate ALL recipes in store (after bulk price updates)

POST /api/recipes-recalculate-all
X-Store-ID: 1

````

---

### ☐ STEP 5: Create Menu

```http
POST /api/menus
````

**Menu:** "Lunch Menu" (11am-3pm)

**Test:** ✓ Menu visible only during time window

---

### ☐ STEP 6: Create Menu Category

```http
POST /api/menu-categories
```

**Category:** "Main Courses" under Lunch Menu

**Test:** ✓ Category appears under menu

---

### ☐ STEP 7: Create Menu Item

There are **3 ways** to create menu items:

#### Option A: Recipe-Based Menu Item (Prepared Dishes)

```http
POST /api/menu-items
Content-Type: application/json
X-Store-ID: 1

{
  "menu_category_id": 1,
  "name": "Grilled Chicken Salad",
  "description": "Fresh salad with grilled chicken",
  "price": 12.99,
  "item_type": "recipe",
  "recipe_id": 1,
  "is_active": true,
  "is_available": true
}
```

**Test:**

- ✓ Cost = $3.98 (from recipe)
- ✓ Food Cost % = 30.6%
- ✓ Profit = $9.01
- ✓ When sold, deducts all recipe ingredients

#### Option B: Product-Based Menu Item (Direct Product Sales) ✨ NEW

```http
POST /api/menu-items
Content-Type: application/json
X-Store-ID: 1

{
  "menu_category_id": 2,
  "name": "Coca Cola",
  "description": "Refreshing soda",
  "price": 3.00,
  "item_type": "product",
  "product_id": 2,
  "is_active": true,
  "is_available": true
}
```

**Test:**

- ✓ Cost = Product's purchase price (auto-calculated)
- ✓ When sold, deducts single product stock
- ✓ No recipe required!

**Use product-based menu items for:**

- Drinks (bottled/canned)
- Packaged items
- Products sold as-is without preparation

#### Option C: Simple Menu Item (Manual Cost)

```http
POST /api/menu-items
Content-Type: application/json
X-Store-ID: 1

{
  "menu_category_id": 3,
  "name": "Delivery Fee",
  "price": 5.00,
  "cost": 2.00,
  "item_type": "simple",
  "is_active": true
}
```

**Test:**

- ✓ Cost = Manual entry
- ✓ No automatic stock deduction

---

### ☐ STEP 8: Create Sale (POS)

#### Option A: Regular Product Sale (Existing)

```http
POST /api/orders
Content-Type: application/json
X-Store-ID: 1

{
  "items": [
    {
      "product_id": 1,
      "name": "Chicken Breast",
      "price": 10.00,
      "qte": 2
    }
  ],
  "discount": 0,
  "advance": 20.00
}
```

#### Option B: Restaurant Order (Menu Items) ✨ NEW

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
      "menu_item_id": 2,
      "quantity": 1
    }
  ],
  "customer_id": 5,
  "discount": 2.00,
  "advance": 20.00,
  "note": "Table 8"
}
```

**Sale Example:** 2× Grilled Chicken Salad = $25.98

**Test - CRITICAL!**

- ✓ Order created
- ✓ Chicken stock: 50.00 → 49.45 KG ⚡ (recipe-based item)
- ✓ Rice stock: 100.00 → 99.70 KG ⚡
- ✓ Lettuce stock: 20.00 → 19.77 KG ⚡
- ✓ Tomatoes stock: 30.00 → 29.895 KG ⚡
- ✓ Oil stock: 10.00 → 9.96 L ⚡
- ✓ Coca Cola stock: 100 → 99 pcs ⚡ (product-based item)
- ✓ Stock movements created automatically
- ✓ Sale profit calculated correctly

**Key Difference:**

- **Regular Order**: Uses `product_id` from products table
- **Restaurant Order**: Uses `menu_item_id` from menu_items table
- Stock deduction happens automatically for both!

---

### ☐ STEP 9: Verify Everything

**Stock Movements:**

```http
GET /api/stock-movements?order_sale_id={id}
```

✓ 5 movements (type: 'sale', direction: 'out')

**Updated Stock:**

```http
GET /api/store-products?store_id=1
```

✓ All products show reduced stock

**Profitability:**

- Revenue: $25.98
- Cost: $7.96
- Profit: $18.02 (69.4% margin)

---

## 🧪 Bonus Test: Weighted Average Cost

### ☐ Second Purchase at Different Price

```http
POST /api/order-purchases
```

**Purchase:** 30 KG Chicken @ $12/KG (higher price)

**Expected Results:**

- ✓ New weighted avg: $10.76/KG
    - Calculation: ($494.50 + $360) / 79.45 KG
- ✓ Recipe cost updates: $3.98 → $4.25 ⚡
- ✓ Menu item cost updates: $4.25 ⚡
- ✓ Food cost % updates: 32.7% ⚡
- ✓ Profit updates: $8.74 ⚡

**This proves:** Automatic cost cascading works! 🎉

---

## 🎯 Critical Success Indicators

### ✅ You Know It's Working When:

1. **Products:** Stock quantities visible
2. **Recipes:** Total cost auto-calculated
3. **Menu Items:** Cost auto-calculated from recipe OR product ✨
4. **Sales:** Stock automatically deducts
5. **Costs:** Update automatically when prices change
6. **Profitability:** Calculated in real-time
7. **Restaurant Orders:** Menu items sold with automatic stock deduction ✨

### ❌ Red Flags (Something's Wrong):

1. Stock doesn't decrease after sale
2. Recipe cost is $0.00
3. Menu item cost ≠ recipe cost (for recipe-based items)
4. Menu item cost ≠ product cost (for product-based items) ✨
5. No stock movements created
6. Costs don't update after new purchase

---

## 📊 Expected Numbers (Reference)

### After Step 3 (Purchase):

| Product  | Stock  | Cost   |
| -------- | ------ | ------ |
| Chicken  | 50 KG  | $10.00 |
| Rice     | 100 KG | $2.50  |
| Lettuce  | 20 KG  | $3.00  |
| Tomatoes | 30 KG  | $4.00  |
| Oil      | 10 L   | $15.00 |

### After Step 4 (Recipe):

- Total Cost: **$3.98**
- Cost Breakdown:
    - Chicken: $2.75 (69%)
    - Rice: $0.375 (9%)
    - Lettuce: $0.345 (9%)
    - Tomatoes: $0.21 (5%)
    - Oil: $0.30 (8%)

### After Step 8 (Sale of 2 servings):

| Product  | Before | After  | Deducted |
| -------- | ------ | ------ | -------- |
| Chicken  | 50.00  | 49.45  | 0.55 KG  |
| Rice     | 100.00 | 99.70  | 0.30 KG  |
| Lettuce  | 20.00  | 19.77  | 0.23 KG  |
| Tomatoes | 30.00  | 29.895 | 0.105 KG |
| Oil      | 10.00  | 9.96   | 0.04 L   |

---

## 🔧 Quick Debug Commands

### Check Stock Levels

```sql
SELECT p.name, sp.stock, sp.cost
FROM store_products sp
JOIN products p ON p.id = sp.product_id
WHERE sp.store_id = 1;
```

### Check Stock Movements

```sql
SELECT sm.type, p.name, sm.quantity, sm.created_at
FROM stock_movements sm
JOIN products p ON p.id = sm.product_id
WHERE sm.type = 'sale'
ORDER BY sm.created_at DESC
LIMIT 10;
```

### Preview Stock Deduction (Before Sale)

```http
POST /api/stock-deductions/preview
{
  "menu_item_id": 1,
  "quantity": 1
}
```

### View Menu Item Details

```http
GET /api/menu-items/{id}
```

Returns menu item with cost, profit margin, and linked recipe/product.

### Create Restaurant Order ✨ NEW

```http
POST /api/restaurant-orders
Content-Type: application/json
X-Store-ID: 1

{
  "menu_items": [
    {"menu_item_id": 1, "quantity": 2}
  ],
  "discount": 0,
  "advance": 20
}
```

### Force Recipe Cost Recalculation

```http
POST /api/recipes/{id}/recalculate-cost
```

---

## 🍽️ Menu Item Types Reference

### Understanding item_type Options

| Type             | Link           | Cost Source            | Stock Deduction         | Use Case                        |
| ---------------- | -------------- | ---------------------- | ----------------------- | ------------------------------- |
| **`recipe`**     | Recipe ID      | Recipe ingredients sum | Deducts all ingredients | Prepared dishes (salad, burger) |
| **`product`** ✨ | Product ID     | Product purchase price | Deducts single product  | Drinks, packaged items          |
| **`simple`**     | None           | Manual entry           | No deduction            | Services, fees                  |
| **`combo`**      | Multiple items | Sum of combo items     | Depends on combo setup  | Meal deals                      |

### When to Use Each Type

**Use `recipe` when:**

- Menu item requires multiple ingredients
- Need to track ingredient consumption
- Complex preparation with waste

**Use `product` when:** ✨ NEW

- Selling single products as-is
- Drinks (bottled, canned)
- Packaged items sold without preparation
- 1:1 product-to-menu-item mapping

**Use `simple` when:**

- Service items (delivery fee)
- No inventory tracking needed
- Manual cost management required

### Stock Deduction Behavior

```
Recipe-Based Item (item_type='recipe'):
   Menu Item → Recipe → Ingredients → Products
   Stock deducted from ALL ingredient products

Product-Based Item (item_type='product'): ✨ NEW
   Menu Item → Product
   Stock deducted from SINGLE product

Simple Item (item_type='simple'):
   No automatic stock deduction
```

---

## 🚀 Ready to Start?

**Before you begin:**

```bash
php artisan migrate
```

This adds product-based menu item support.

**Then:**

1. Print this checklist
2. Follow steps 1-9 in order
3. Check each test point
4. Verify all ✓ marks are green
5. Try both recipe-based AND product-based menu items ✨
6. Celebrate when it works! 🎉

**Estimated Time:** 30-45 minutes for complete workflow

---

**Document:** Quick Start Checklist  
**Version:** 2.0  
**Last Updated:** May 23, 2026  
**Changes:** Added product-based menu items and restaurant order endpoint
