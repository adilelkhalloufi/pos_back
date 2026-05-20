# Quick Start Checklist ✅

## From Product to Sale - Quick Reference

**Date:** May 20, 2026

---

## 📋 9-Step Process

### ☐ STEP 1: Create Products
```http
POST /api/products
```
**Create:** Chicken Breast, Rice, Lettuce, Tomatoes, Olive Oil

**Test:** ✓ Products appear in list

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
```http
POST /api/recipes
```
**Recipe:** "Grilled Chicken Salad"
- 250g Chicken (10% waste)
- 150g Rice
- 100g Lettuce (15% waste)
- 50g Tomatoes (5% waste)
- 20ml Olive Oil

**Test:** ✓ Total cost = $3.98 calculated automatically

---

### ☐ STEP 5: Create Menu
```http
POST /api/menus
```
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
```http
POST /api/menu-items
```
**Item:** "Grilled Chicken Salad"
- Price: $12.99
- Linked to Recipe (ID: 1)

**Test:** 
- ✓ Cost = $3.98 (from recipe)
- ✓ Food Cost % = 30.6%
- ✓ Profit = $9.01

---

### ☐ STEP 8: Create Sale (POS)
```http
POST /api/order-sales
```
**Sale:** 2× Grilled Chicken Salad = $25.98

**Test - CRITICAL!**
- ✓ Order created
- ✓ Chicken stock: 50.00 → 49.45 KG ⚡
- ✓ Rice stock: 100.00 → 99.70 KG ⚡
- ✓ Lettuce stock: 20.00 → 19.77 KG ⚡
- ✓ Tomatoes stock: 30.00 → 29.895 KG ⚡
- ✓ Oil stock: 10.00 → 9.96 L ⚡
- ✓ 5 stock movements created
- ✓ Sale profit = $18.02

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
3. **Menu Items:** Cost = Recipe cost
4. **Sales:** Stock automatically deducts
5. **Costs:** Update automatically when prices change
6. **Profitability:** Calculated in real-time

### ❌ Red Flags (Something's Wrong):

1. Stock doesn't decrease after sale
2. Recipe cost is $0.00
3. Menu item cost ≠ recipe cost
4. No stock movements created
5. Costs don't update after new purchase

---

## 📊 Expected Numbers (Reference)

### After Step 3 (Purchase):
| Product | Stock | Cost |
|---------|-------|------|
| Chicken | 50 KG | $10.00 |
| Rice | 100 KG | $2.50 |
| Lettuce | 20 KG | $3.00 |
| Tomatoes | 30 KG | $4.00 |
| Oil | 10 L | $15.00 |

### After Step 4 (Recipe):
- Total Cost: **$3.98**
- Cost Breakdown:
  - Chicken: $2.75 (69%)
  - Rice: $0.375 (9%)
  - Lettuce: $0.345 (9%)
  - Tomatoes: $0.21 (5%)
  - Oil: $0.30 (8%)

### After Step 8 (Sale of 2 servings):
| Product | Before | After | Deducted |
|---------|--------|-------|----------|
| Chicken | 50.00 | 49.45 | 0.55 KG |
| Rice | 100.00 | 99.70 | 0.30 KG |
| Lettuce | 20.00 | 19.77 | 0.23 KG |
| Tomatoes | 30.00 | 29.895 | 0.105 KG |
| Oil | 10.00 | 9.96 | 0.04 L |

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

### Force Recipe Cost Recalculation
```http
POST /api/recipes/{id}/recalculate-cost
```

---

## 🚀 Ready to Start?

1. Print this checklist
2. Follow steps 1-9 in order
3. Check each test point
4. Verify all ✓ marks are green
5. Celebrate when it works! 🎉

**Estimated Time:** 30-45 minutes for complete workflow

---

**Document:** Quick Start Checklist  
**Version:** 1.0  
**Last Updated:** May 20, 2026
