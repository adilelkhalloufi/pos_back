# Complete Workflow Guide: Product → Recipe → Sale

## End-to-End Process for Restaurant ERP System

**Date:** May 20, 2026  
**Purpose:** Step-by-step guide from creating a product to selling a menu item

---

## 📋 Overview

This guide walks you through the complete process of:
1. Creating products (ingredients)
2. Setting up unit conversions
3. Creating recipes
4. Building menu structure
5. Selling through POS
6. Verifying automatic stock deduction and cost tracking

---

## 🎯 Complete Flow Diagram

```
STEP 1: CREATE PRODUCTS (Ingredients)
    ↓
STEP 2: SETUP UNIT CONVERSIONS (if needed)
    ↓
STEP 3: PURCHASE PRODUCTS (Add stock)
    ↓
STEP 4: CREATE RECIPE (Define ingredients & quantities)
    ↓
STEP 5: CREATE MENU STRUCTURE (Menu → Category)
    ↓
STEP 6: CREATE MENU ITEM (Link to Recipe)
    ↓
STEP 7: CREATE SALE (POS Transaction)
    ↓
STEP 8: AUTOMATIC STOCK DEDUCTION ✨
    ↓
STEP 9: VERIFY COSTS & PROFITABILITY
```

---

## STEP 1: Create Products (Ingredients) 🥩

### What You're Doing
Creating the raw ingredients that will be used in recipes.

### API Endpoint
```http
POST /api/products
```

### Request Body Example
```json
{
  "name": "Chicken Breast",
  "sku": "CHKN-001",
  "category_id": 1,
  "unit_id": 1,  // Kilogram
  "store_id": 1,
  "description": "Fresh chicken breast",
  "is_active": true
}
```

### Create Multiple Products
```json
// Product 1: Chicken Breast (KG)
{
  "name": "Chicken Breast",
  "sku": "CHKN-001",
  "unit_id": 1  // Kilogram
}

// Product 2: Rice (KG)
{
  "name": "Rice",
  "sku": "RICE-001",
  "unit_id": 1  // Kilogram
}

// Product 3: Lettuce (KG)
{
  "name": "Lettuce",
  "sku": "LET-001",
  "unit_id": 1  // Kilogram
}

// Product 4: Tomatoes (KG)
{
  "name": "Tomatoes",
  "sku": "TOM-001",
  "unit_id": 1  // Kilogram
}

// Product 5: Olive Oil (Liter)
{
  "name": "Olive Oil",
  "sku": "OIL-001",
  "unit_id": 2  // Liter
}
```

### ✅ Frontend Testing Checklist

- [ ] Product appears in products list
- [ ] Product details show correctly (name, SKU, unit)
- [ ] Product is searchable
- [ ] Product can be edited
- [ ] Product status (active/inactive) works

### Expected Database State
```sql
-- Check products table
SELECT id, name, sku FROM products;

-- Expected:
-- 1 | Chicken Breast | CHKN-001
-- 2 | Rice           | RICE-001
-- 3 | Lettuce        | LET-001
-- 4 | Tomatoes       | TOM-001
-- 5 | Olive Oil      | OIL-001
```

---

## STEP 2: Setup Unit Conversions (Optional) 🔄

### What You're Doing
Setting up conversions so recipes can use different units than inventory tracking.

### Quick Setup (RECOMMENDED)
```bash
php artisan db:seed --class=UnitsAndConversionsSeeder
```

**This automatically creates:**
- ✅ 27 Units (kg, g, mg, lb, oz, L, ml, gal, piece, dozen, etc.)
- ✅ 70+ Conversions between all compatible units
- ✅ Common restaurant units (bottle, can, portion, serving)

**Verification:**
```sql
-- Check units created
SELECT COUNT(*) FROM units;
-- Expected: 27

-- Check conversions created
SELECT COUNT(*) FROM unit_conversions;
-- Expected: 70+

-- Test KG to Gram conversion
SELECT conversion_factor FROM unit_conversions 
WHERE from_unit_id = 1 AND to_unit_id = 2;
-- Expected: 1000
```

### Manual Setup (Alternative)

### API Endpoint
```http
POST /api/unit-conversions/create-standard
```

### Common Conversions to Create
```json
// Weight Conversions
{
  "from_unit_id": 1,  // Kilogram
  "to_unit_id": 3,    // Gram
  "conversion_factor": 1000,
  "store_id": 1
}

// Volume Conversions
{
  "from_unit_id": 2,  // Liter
  "to_unit_id": 4,    // Milliliter
  "conversion_factor": 1000,
  "store_id": 1
}
```

### Pre-built Standard Conversions
```http
POST /api/unit-conversions/create-standard
{
  "store_id": 1
}
```

This automatically creates:
- 1 KG = 1000 Grams
- 1 Liter = 1000 Milliliters
- Common conversions

### ✅ Frontend Testing Checklist

- [ ] Conversions list displays
- [ ] Can create custom conversions
- [ ] Can test conversion (convert X units to Y units)
- [ ] Conversion validation works (prevent incompatible units)

### Test Conversion API
```http
POST /api/unit-conversions/convert
{
  "quantity": 2.5,
  "from_unit_id": 1,  // Kilogram
  "to_unit_id": 3,    // Gram
  "store_id": 1
}

Response:
{
  "converted_quantity": 2500,
  "from_unit": "Kilogram",
  "to_unit": "Gram"
}
```

---

## STEP 3: Purchase Products (Add Initial Stock) 📦

### What You're Doing
Adding stock to inventory through purchase orders.

### API Endpoint
```http
POST /api/order-purchases
```

### Request Body Example
```json
{
  "supplier_id": 1,
  "store_id": 1,
  "user_id": 1,
  "status": 7,  // Completed
  "items": [
    {
      "product_id": 1,  // Chicken Breast
      "quantity": 50,   // 50 KG
      "price": 10.00    // $10 per KG
    },
    {
      "product_id": 2,  // Rice
      "quantity": 100,  // 100 KG
      "price": 2.50     // $2.50 per KG
    },
    {
      "product_id": 3,  // Lettuce
      "quantity": 20,   // 20 KG
      "price": 3.00     // $3 per KG
    },
    {
      "product_id": 4,  // Tomatoes
      "quantity": 30,   // 30 KG
      "price": 4.00     // $4 per KG
    },
    {
      "product_id": 5,  // Olive Oil
      "quantity": 10,   // 10 Liters
      "price": 15.00    // $15 per Liter
    }
  ]
}
```

### What Happens Automatically ✨
1. **Stock Movement Created** (type: 'purchase', direction: 'in')
2. **Store Products Updated** with new stock quantities
3. **Weighted Average Cost Calculated** by CostingService
4. **Cost stored** in store_products.cost

### ✅ Frontend Testing Checklist

- [ ] Purchase order created successfully
- [ ] Purchase order appears in list
- [ ] Stock quantities updated (check inventory page)
- [ ] Product costs updated (check product details)
- [ ] Stock movement logged (check stock movements)

### Verify Stock & Cost
```http
GET /api/store-products?store_id=1&product_id=1

Response:
{
  "product_id": 1,
  "store_id": 1,
  "stock": 50,        // 50 KG in stock
  "cost": 10.00,      // Weighted average cost
  "price": 0
}
```

### Expected Database State
```sql
-- Check store_products
SELECT product_id, stock, cost FROM store_products WHERE store_id = 1;

-- Expected:
-- 1 | 50.00  | 10.00  (Chicken: 50kg @ $10/kg)
-- 2 | 100.00 | 2.50   (Rice: 100kg @ $2.50/kg)
-- 3 | 20.00  | 3.00   (Lettuce: 20kg @ $3/kg)
-- 4 | 30.00  | 4.00   (Tomatoes: 30kg @ $4/kg)
-- 5 | 10.00  | 15.00  (Oil: 10L @ $15/L)

-- Check stock_movements
SELECT type, product_id, quantity, unit_cost FROM stock_movements;

-- Expected: 5 purchase movements created
```

---

## STEP 4: Create Recipe 👨‍🍳

### What You're Doing
Defining a recipe with ingredients, quantities, and preparation instructions.

### API Endpoint
```http
POST /api/recipes
```

### Request Body Example: "Grilled Chicken Salad"
```json
{
  "name": "Grilled Chicken Salad",
  "description": "Fresh grilled chicken breast on mixed greens",
  "instructions": "1. Grill chicken breast\n2. Slice into strips\n3. Arrange on greens\n4. Drizzle with olive oil",
  "yield_quantity": 1,
  "yield_unit_id": 6,  // Portion/Serving
  "preparation_time_minutes": 10,
  "cooking_time_minutes": 15,
  "skill_level": "easy",
  "store_id": 1,
  "user_id": 1,
  "is_active": true,
  "ingredients": [
    {
      "product_id": 1,        // Chicken Breast
      "quantity": 250,        // 250 grams
      "unit_id": 3,           // Gram
      "waste_percentage": 10, // 10% trim loss
      "preparation_notes": "Trim and grill until 165°F",
      "is_optional": false
    },
    {
      "product_id": 2,        // Rice
      "quantity": 150,        // 150 grams
      "unit_id": 3,           // Gram
      "waste_percentage": 0,
      "preparation_notes": "Cook until tender",
      "is_optional": false
    },
    {
      "product_id": 3,        // Lettuce
      "quantity": 100,        // 100 grams
      "unit_id": 3,           // Gram
      "waste_percentage": 15, // 15% waste (outer leaves)
      "preparation_notes": "Wash and chop",
      "is_optional": false
    },
    {
      "product_id": 4,        // Tomatoes
      "quantity": 50,         // 50 grams
      "unit_id": 3,           // Gram
      "waste_percentage": 5,
      "preparation_notes": "Dice",
      "is_optional": false
    },
    {
      "product_id": 5,        // Olive Oil
      "quantity": 20,         // 20 milliliters
      "unit_id": 4,           // Milliliter
      "waste_percentage": 0,
      "preparation_notes": "Drizzle on top",
      "is_optional": false
    }
  ]
}
```

### What Happens Automatically ✨
1. **Recipe Created** in recipes table
2. **Recipe Ingredients Created** in recipe_ingredients table
3. **Unit Conversions Applied** (Grams → Kilograms, ML → Liters)
4. **Ingredient Costs Calculated** with waste percentage
5. **Total Recipe Cost Calculated** automatically
6. **Cost Per Serving Calculated** (total_cost / yield_quantity)

### Cost Calculation Breakdown
```
Ingredient Costs (with waste):

Chicken: 250g × (1 + 10%) = 275g actual
         0.275 kg × $10.00/kg = $2.75

Rice:    150g × (1 + 0%) = 150g actual
         0.150 kg × $2.50/kg = $0.375

Lettuce: 100g × (1 + 15%) = 115g actual
         0.115 kg × $3.00/kg = $0.345

Tomato:  50g × (1 + 5%) = 52.5g actual
         0.0525 kg × $4.00/kg = $0.21

Oil:     20ml × (1 + 0%) = 20ml actual
         0.020 L × $15.00/L = $0.30

Total Recipe Cost: $3.98
Cost Per Serving: $3.98 / 1 = $3.98
```

### ✅ Frontend Testing Checklist

- [ ] Recipe created successfully
- [ ] Recipe appears in recipes list
- [ ] All ingredients display with quantities
- [ ] Total cost shows correctly ($3.98)
- [ ] Cost per serving displays
- [ ] Can edit recipe
- [ ] Can add/remove ingredients
- [ ] Waste percentage affects cost calculation

### Verify Recipe Details
```http
GET /api/recipes/{recipe_id}

Response:
{
  "id": 1,
  "name": "Grilled Chicken Salad",
  "total_cost": 3.98,
  "cost_per_serving": 3.98,
  "yield_quantity": 1,
  "ingredients": [
    {
      "product_id": 1,
      "product_name": "Chicken Breast",
      "quantity": 250,
      "unit": "Gram",
      "waste_percentage": 10,
      "cost": 2.75,  // Cost including waste
      "effective_quantity": 275  // Quantity including waste
    },
    // ... other ingredients
  ]
}
```

### Expected Database State
```sql
-- Check recipes
SELECT id, name, total_cost, cost_per_serving FROM recipes;

-- Expected:
-- 1 | Grilled Chicken Salad | 3.98 | 3.98

-- Check recipe_ingredients
SELECT recipe_id, product_id, quantity, waste_percentage, cost 
FROM recipe_ingredients WHERE recipe_id = 1;

-- Expected: 5 ingredients with calculated costs
```

---

## STEP 5: Create Menu Structure 📋

### What You're Doing
Building the menu hierarchy: Menu → Categories → Items

### 5.1: Create Menu

```http
POST /api/menus
```

```json
{
  "name": "Lunch Menu",
  "description": "Available during lunch hours",
  "type": "lunch",
  "is_active": true,
  "display_order": 1,
  "available_from_time": "11:00:00",
  "available_to_time": "15:00:00",
  "store_id": 1
}
```

### 5.2: Create Menu Category

```http
POST /api/menu-categories
```

```json
{
  "menu_id": 1,  // From step 5.1
  "name": "Main Courses",
  "description": "Hearty meals",
  "display_order": 1,
  "is_active": true
}
```

### ✅ Frontend Testing Checklist

- [ ] Menu created and appears in list
- [ ] Menu time restrictions work (only visible 11am-3pm)
- [ ] Category created under menu
- [ ] Menu hierarchy displays correctly (Menu → Category)
- [ ] Display order works

---

## STEP 6: Create Menu Item (Link to Recipe) 🍽️

### What You're Doing
Creating a sellable menu item and linking it to the recipe.

### API Endpoint
```http
POST /api/menu-items
```

### Request Body Example
```json
{
  "menu_category_id": 1,  // From step 5.2
  "name": "Grilled Chicken Salad",
  "description": "Fresh grilled chicken on mixed greens with rice",
  "price": 12.99,  // Selling price
  "item_type": "recipe",
  "recipe_id": 1,  // From step 4
  "store_id": 1,
  "is_active": true,
  "is_available": true,
  "preparation_time_minutes": 25,
  "display_order": 1
}
```

### What Happens Automatically ✨
1. **Menu Item Created**
2. **Cost Copied from Recipe** ($3.98)
3. **Food Cost % Calculated** (cost/price × 100 = 30.6%)
4. **Profit Calculated** (price - cost = $9.01)
5. **Margin Calculated** ((price - cost) / price × 100 = 69.4%)

### Profitability Analysis
```
Menu Item: Grilled Chicken Salad
Price:       $12.99
Cost:        $3.98  (from recipe)
Profit:      $9.01
Margin:      69.4%
Food Cost %: 30.6%  (ideal is 28-35%)
```

### ✅ Frontend Testing Checklist

- [ ] Menu item created successfully
- [ ] Menu item appears under correct category
- [ ] Price displays correctly ($12.99)
- [ ] Cost displays correctly ($3.98)
- [ ] Food cost percentage shows (30.6%)
- [ ] Profit/margin displays
- [ ] Item is available for sale in POS
- [ ] Recipe link works (can view recipe from menu item)

### Verify Menu Item
```http
GET /api/menu-items/{menu_item_id}

Response:
{
  "id": 1,
  "name": "Grilled Chicken Salad",
  "price": 12.99,
  "cost": 3.98,
  "food_cost_percentage": 30.6,
  "profit": 9.01,
  "margin": 69.4,
  "recipe_id": 1,
  "recipe": {
    "id": 1,
    "name": "Grilled Chicken Salad",
    "total_cost": 3.98
  }
}
```

---

## STEP 7: Create Sale (POS Transaction) 💰

### What You're Doing
Selling the menu item through the POS system.

### API Endpoint
```http
POST /api/order-sales
```

### Request Body Example
```json
{
  "customer_id": 1,  // Optional
  "store_id": 1,
  "user_id": 1,
  "paid_method_id": 1,  // Cash/Card
  "status": 3,  // Paid
  "items": [
    {
      "menu_item_id": 1,  // Grilled Chicken Salad
      "quantity": 2,      // Selling 2 servings
      "price": 12.99
    }
  ]
}
```

### What Happens Automatically ✨ (MAGIC!)

1. **Order Sale Created**
2. **Order Items Created**
3. **StockDeductionService Triggered** (via Observer)
4. **Recipe Decomposed** into ingredients
5. **For Each Ingredient:**
   - Unit conversion applied (if needed)
   - Stock deducted from inventory
   - Stock movement created (type: 'sale')
   - Store product stock updated
   - Weighted average cost maintained
6. **Theoretical Consumption Tracked**
7. **Invoice Created** (optional)

### Stock Deduction Breakdown (for 2 servings)
```
Menu Item: Grilled Chicken Salad × 2
Recipe Yield: 1 serving
Total Servings: 2

Ingredient Deductions:

Chicken Breast:
- Recipe needs: 250g × 2 = 500g per serving
- With 10% waste: 500g × 1.10 = 550g actual
- Stock deducted: 0.55 KG (converted from grams)
- Stock before: 50.00 KG
- Stock after: 49.45 KG

Rice:
- Recipe needs: 150g × 2 = 300g
- With 0% waste: 300g
- Stock deducted: 0.30 KG
- Stock before: 100.00 KG
- Stock after: 99.70 KG

Lettuce:
- Recipe needs: 100g × 2 = 200g
- With 15% waste: 200g × 1.15 = 230g actual
- Stock deducted: 0.23 KG
- Stock before: 20.00 KG
- Stock after: 19.77 KG

Tomatoes:
- Recipe needs: 50g × 2 = 100g
- With 5% waste: 100g × 1.05 = 105g actual
- Stock deducted: 0.105 KG
- Stock before: 30.00 KG
- Stock after: 29.895 KG

Olive Oil:
- Recipe needs: 20ml × 2 = 40ml
- With 0% waste: 40ml
- Stock deducted: 0.04 L (converted from ml)
- Stock before: 10.00 L
- Stock after: 9.96 L
```

### ✅ Frontend Testing Checklist - CRITICAL!

**Order Creation:**
- [ ] Order created successfully
- [ ] Order number generated
- [ ] Order total calculated correctly (2 × $12.99 = $25.98)
- [ ] Order status is "Paid"
- [ ] Order appears in sales list

**Stock Deduction (VERIFY THIS!):**
- [ ] Chicken stock reduced: 50.00 → 49.45 KG ✅
- [ ] Rice stock reduced: 100.00 → 99.70 KG ✅
- [ ] Lettuce stock reduced: 20.00 → 19.77 KG ✅
- [ ] Tomatoes stock reduced: 30.00 → 29.895 KG ✅
- [ ] Oil stock reduced: 10.00 → 9.96 L ✅

**Stock Movements:**
- [ ] 5 stock movements created (one per ingredient)
- [ ] Each movement shows:
  - type: 'sale'
  - direction: 'out'
  - quantity deducted
  - unit_cost (weighted average)
  - referenceable: order_sale_id

**Cost Tracking:**
- [ ] Sale cost tracked: 2 × $3.98 = $7.96
- [ ] Sale revenue: $25.98
- [ ] Gross profit: $25.98 - $7.96 = $18.02
- [ ] Profit margin: 69.4%

### Verify Stock After Sale
```http
GET /api/store-products?store_id=1

Response (check each product):
[
  {
    "product_id": 1,
    "product_name": "Chicken Breast",
    "stock": 49.45,  // Reduced from 50.00
    "cost": 10.00
  },
  {
    "product_id": 2,
    "product_name": "Rice",
    "stock": 99.70,  // Reduced from 100.00
    "cost": 2.50
  },
  {
    "product_id": 3,
    "product_name": "Lettuce",
    "stock": 19.77,  // Reduced from 20.00
    "cost": 3.00
  },
  // ... etc
]
```

### Verify Stock Movements
```http
GET /api/stock-movements?order_sale_id={sale_id}

Response:
[
  {
    "product_id": 1,
    "product_name": "Chicken Breast",
    "type": "sale",
    "direction": "out",
    "quantity": 0.55,
    "unit_cost": 10.00,
    "total_cost": 5.50,
    "previous_stock": 50.00,
    "new_stock": 49.45
  },
  // ... 4 more movements for other ingredients
]
```

---

## STEP 8: Verify Automatic Stock Deduction ✨

### What to Check

### 8.1: Database Verification
```sql
-- Check store_products stock levels
SELECT p.name, sp.stock, sp.cost
FROM store_products sp
JOIN products p ON p.id = sp.product_id
WHERE sp.store_id = 1;

-- Expected:
-- Chicken Breast | 49.45  | 10.00
-- Rice           | 99.70  | 2.50
-- Lettuce        | 19.77  | 3.00
-- Tomatoes       | 29.895 | 4.00
-- Olive Oil      | 9.96   | 15.00

-- Check stock movements (should have 5 new movements)
SELECT sm.type, p.name, sm.quantity, sm.unit_cost, sm.total_cost
FROM stock_movements sm
JOIN products p ON p.id = sm.product_id
WHERE sm.type = 'sale'
ORDER BY sm.created_at DESC
LIMIT 5;

-- Check theoretical consumption
SELECT tc.product_id, tc.theoretical_quantity, tc.actual_quantity
FROM theoretical_consumption tc
WHERE tc.date = CURDATE();
```

### 8.2: Frontend Verification Screens

**Inventory Dashboard:**
- Stock levels updated in real-time
- Stock movement history shows deductions
- Low stock alerts (if applicable)

**Sales Report:**
- Sale recorded with correct items
- Cost of goods sold (COGS) calculated
- Profit per transaction displayed

**Product Detail Page:**
- Stock quantity updated
- Recent stock movements visible
- Cost history maintained

---

## STEP 9: Verify Costs & Profitability 📊

### 9.1: Sale Profitability
```
Sale Analysis (2 servings):

Revenue:           $25.98
Cost (2 × $3.98):  $7.96
Gross Profit:      $18.02
Profit Margin:     69.4%
Food Cost %:       30.6%

✅ Healthy profit margin (target: 65-75%)
✅ Food cost within range (target: 28-35%)
```

### 9.2: Recipe Cost Accuracy
```http
GET /api/recipes/1/cost-breakdown

Response:
{
  "recipe_name": "Grilled Chicken Salad",
  "total_cost": 3.98,
  "cost_per_serving": 3.98,
  "ingredients": [
    {
      "name": "Chicken Breast",
      "quantity": 250,
      "unit": "Gram",
      "waste_percentage": 10,
      "effective_quantity": 275,
      "unit_cost": 10.00,
      "total_cost": 2.75,
      "percentage_of_recipe": 69.1%
    },
    // ... other ingredients
  ]
}
```

### 9.3: Stock Valuation
```http
GET /api/reports/inventory-valuation?store_id=1

Response:
{
  "total_inventory_value": 1247.20,
  "by_product": [
    {
      "product": "Chicken Breast",
      "stock": 49.45,
      "cost": 10.00,
      "value": 494.50
    },
    {
      "product": "Rice",
      "stock": 99.70,
      "cost": 2.50,
      "value": 249.25
    },
    // ... etc
  ]
}
```

### ✅ Final Verification Checklist

**Recipe System:**
- [ ] Recipe created with all ingredients
- [ ] Costs calculate automatically
- [ ] Waste percentage included in costs
- [ ] Unit conversions work correctly

**Menu System:**
- [ ] Menu item linked to recipe
- [ ] Price and cost displayed
- [ ] Food cost percentage calculated
- [ ] Profitability visible

**Stock Management:**
- [ ] Stock deducts automatically on sale
- [ ] Correct quantities deducted (with waste)
- [ ] Unit conversions applied
- [ ] Stock movements logged
- [ ] Weighted average cost maintained

**Cost Tracking:**
- [ ] Sale cost = recipe cost × quantity
- [ ] Profit calculated correctly
- [ ] COGS tracked
- [ ] Inventory value accurate

---

## 🔄 Test Scenario: Second Purchase at Different Price

### Objective
Verify weighted average cost calculation works correctly.

### Steps

1. **Make Second Purchase (Higher Price)**
```http
POST /api/order-purchases
{
  "supplier_id": 1,
  "store_id": 1,
  "items": [
    {
      "product_id": 1,  // Chicken Breast
      "quantity": 30,   // 30 KG
      "price": 12.00    // $12 per KG (higher than $10)
    }
  ]
}
```

2. **Verify Weighted Average Cost**
```
Before:
- Stock: 49.45 KG at $10.00/KG = $494.50 value

New Purchase:
- Quantity: 30 KG at $12.00/KG = $360.00 value

Weighted Average:
- Total Value: $494.50 + $360.00 = $854.50
- Total Quantity: 49.45 + 30 = 79.45 KG
- New Average Cost: $854.50 / 79.45 = $10.76/KG

✅ Stock: 79.45 KG
✅ Cost: $10.76/KG (automatically calculated)
```

3. **Check Recipe Cost Update**
```http
GET /api/recipes/1

Response:
{
  "total_cost": 4.25,  // UPDATED from $3.98!
  "ingredients": [
    {
      "name": "Chicken Breast",
      "cost": 2.96  // UPDATED (0.275 kg × $10.76)
    },
    // ... other ingredients unchanged
  ]
}
```

4. **Check Menu Item Cost Update**
```http
GET /api/menu-items/1

Response:
{
  "cost": 4.25,  // UPDATED automatically!
  "price": 12.99,
  "food_cost_percentage": 32.7%,  // UPDATED
  "profit": 8.74,  // UPDATED
  "margin": 67.3%  // UPDATED
}
```

### ✅ Verification Points
- [ ] Weighted average cost calculated: $10.76/KG
- [ ] Recipe cost updated automatically: $4.25
- [ ] Menu item cost updated automatically: $4.25
- [ ] Food cost % recalculated: 32.7%
- [ ] Profit recalculated: $8.74
- [ ] All cascading updates work!

---

## 🚨 Common Issues & Troubleshooting

### Issue 1: Stock Not Deducting
**Symptoms:** Sale created but stock unchanged

**Check:**
1. Is `OrderSaleObserver` registered?
2. Check `EventServiceProvider.php`
3. Check stock_movements table for sale records
4. Verify recipe has ingredients
5. Check menu item is linked to recipe (item_type: 'recipe')

**Debug:**
```php
// In tinker:
$sale = OrderSale::find(1);
event(new \App\Events\OrderSaleCreated($sale));
// Check if stock deducts
```

### Issue 2: Wrong Quantities Deducted
**Symptoms:** Stock deducts but wrong amounts

**Check:**
1. Unit conversions setup correctly?
2. Recipe quantities in correct units?
3. Waste percentage applied?
4. Yield quantity correct?

**Debug:**
```http
POST /api/stock-deductions/preview
{
  "menu_item_id": 1,
  "quantity": 1
}
// See what WOULD be deducted before actual sale
```

### Issue 3: Costs Not Updating
**Symptoms:** Recipe/menu costs don't update after purchase

**Check:**
1. Is `PriceChangeService` triggered?
2. Check if products are linked to recipes
3. Verify `CostingService` is updating store_products.cost

**Fix:**
```http
POST /api/recipes/{id}/recalculate-cost
// Force recalculation
```

### Issue 4: Unit Conversion Errors
**Symptoms:** "Cannot convert between units" error

**Check:**
1. Are conversions setup? (`unit_conversions` table)
2. Are units compatible? (can't convert KG to Liter)
3. Is ConversionService working?

**Fix:**
```http
POST /api/unit-conversions/create-standard
{
  "store_id": 1
}
// Create standard conversions
```

---

## 📱 Frontend Components Needed

### 1. Product Management
- Product list with search/filter
- Product create/edit form
- Product detail view with stock info
- Unit selector dropdown

### 2. Recipe Management
- Recipe list with cost display
- Recipe builder (drag-drop ingredients)
- Ingredient quantity/waste inputs
- Cost breakdown display
- Recipe preview/print

### 3. Menu Management
- Menu hierarchy tree view
- Menu item cards with images
- Price/cost/margin display
- Availability toggle
- Time-based menu filtering

### 4. POS System
- Menu item selection
- Shopping cart
- Quantity selector
- Order summary
- Payment methods
- Receipt printing

### 5. Inventory Dashboard
- Real-time stock levels
- Low stock alerts
- Stock movement history
- Inventory valuation
- Stock adjustments

### 6. Reports
- Sales report with profitability
- Recipe profitability ranking
- Inventory valuation
- Food cost percentage
- Stock movement log

---

## 🎓 Key Learnings

### 1. Weighted Average Costing
- Every purchase updates average cost
- All recipes using that product update automatically
- Historical cost maintained in stock_movements

### 2. Unit Conversions
- Essential for flexible recipe creation
- Conversions happen automatically during stock deduction
- Store-specific overrides possible

### 3. Waste Percentage
- Always include waste in recipe costs
- Reflects real-world ingredient usage
- Improves cost accuracy

### 4. Automatic Stock Deduction
- No manual inventory management needed
- Real-time stock accuracy
- Full audit trail in stock_movements

### 5. Cost Cascading
- Product cost change → Recipe cost update → Menu item update
- Everything stays synchronized automatically
- Profitability always accurate

---

## 🚀 Next Steps

After completing this workflow:

1. **Test Edge Cases:**
   - Insufficient stock scenarios
   - Multiple stores
   - Combo meals (Phase 4)
   - Returns/refunds

2. **Implement Phase 4:** Combo Meals
3. **Implement Phase 5:** Waste & Expiration Tracking
4. **Implement Phase 6:** Financial Reporting

---

**Last Updated:** May 20, 2026  
**Status:** Complete end-to-end workflow documented  
**Next:** Start testing with real data!
