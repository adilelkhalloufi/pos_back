# Quick Reference: Recipe & Menu Item Cost Fixes

## 🎯 TWO BUGS FIXED

### Bug #1: Stock Deduction Not Working

**File:** `app/Services/Stock/StockDeductionService.php`
**Problem:** Used wrong property name (`quantity_with_waste` instead of `effective_quantity`)
**Result:** Stock movements showed 0.00 quantity, no stock was actually deducted
**Status:** ✅ FIXED

### Bug #2: Recipe Cost Calculation Wrong

**File:** `app/Models/RecipeIngredient.php`
**Problem:** No unit conversion between recipe units (g, ml) and product units (kg, L)
**Result:** Costs were 1000x too high ($2,774 instead of $2.77)
**Status:** ✅ FIXED

---

## 📊 BEFORE & AFTER

### Stock Deduction Example:

```
BEFORE: Selling "Grilled Chicken Salad"
  Stock Movement: Chicken Breast -0.00g ❌
  Result: Inventory never decreased

AFTER: Selling "Grilled Chicken Salad"
  Stock Movement: Chicken Breast -275.00g ✅
  Result: Inventory correctly decreased
```

### Cost Calculation Example:

```
BEFORE: Recipe Cost = $2,774.75 ❌
  (250g chicken × $8 = $2,000... treating grams as kilograms!)

AFTER: Recipe Cost = $2.77 ✅
  (0.25kg chicken × $8 = $2.00... correct conversion!)
```

---

## 🧪 TESTING COMMANDS

### Test Stock Deduction:

```bash
# Check current stock levels
php tests/check_stock_movements.php

# Make a sale via API
POST /api/sell-menu-items
{
  "items": [{"id": 1, "name": "Test Dish", "price": 12.99, "qte": 1}],
  "total_command": 12.99,
  "total_payment": 12.99
}

# Verify stock decreased
php tests/check_stock_movements.php
```

### Test Cost Calculation:

```bash
# Check and update all costs
php tests/test_complete_cost_update.php

# Or via API
POST /api/recipes/recalculate-all
Headers: X-Store-ID: 1
```

---

## 📝 COST CALCULATION CHECKLIST

When creating recipes, ensure:

1. ✅ **Unit Conversions Exist**
    - Check `unit_conversions` table has records
    - Common: g↔kg, ml↔L, oz↔lb

2. ✅ **Products Have Costs**
    - `StoreProducts.cost` set for inventory items
    - Or `Products.price_buy` as fallback

3. ✅ **Products Have Units**
    - `Products.unit_id` must be set
    - Should match storage unit (kg, L, etc.)

4. ✅ **Recipe Ingredients Complete**
    - Each ingredient has `quantity` and `unit_id`
    - `waste_percentage` optional (default 0%)

---

## 🚀 WORKFLOW

### Creating a Menu Item with Recipe:

1. **Create Recipe**

    ```
    POST /api/recipes
    → Ingredients created
    → Costs calculated (with unit conversion)
    → Recipe total cost calculated
    ```

2. **Create Menu Item**

    ```
    POST /api/menu-items
    → Links to recipe
    → Cost copied from recipe
    → Ready to sell!
    ```

3. **Sell Menu Item**
    ```
    POST /api/sell-menu-items
    → Order created
    → Stock deducted for each ingredient
    → Inventory updated
    ```

---

## 📚 DOCUMENTATION FILES

- **STOCK_DEDUCTION_FIX.md** - Details on stock deduction fix
- **RECIPE_COST_FIX.md** - Details on cost calculation fix
- **tests/check_menu_items_stock.php** - Diagnostic for menu items
- **tests/check_stock_movements.php** - View stock movements
- **tests/test_complete_cost_update.php** - Test cost calculations

---

## ⚠️ COMMON ISSUES

### Stock Not Deducting?

1. Check menu item `item_type`:
    - `'recipe'` - Deducts ingredients ✓
    - `'product'` - Deducts product directly ✓
    - `'simple'` - No deduction (e.g., delivery fee) ✓

2. Check `StoreProducts` records exist
3. Check sufficient stock available

### Costs Still Wrong?

1. Verify unit conversions exist in database
2. Check product `unit_id` is set
3. Run recalculate cost API endpoint
4. Check logs for conversion errors

### Sale Fails?

- Check stock levels (might be insufficient)
- Errors now properly returned (not silently ignored)
- Transaction rolls back if stock deduction fails

---

## ✅ VERIFICATION

Your system is working correctly when:

1. ✅ Stock movements show actual quantities (not 0.00)
2. ✅ Recipe costs are reasonable (dollars, not thousands)
3. ✅ Food cost % is 20-40% (typical for restaurants)
4. ✅ Profit margins are positive
5. ✅ Inventory decreases after sales

---

## 🎉 YOU'RE ALL SET!

Both critical bugs are now fixed:

- Stock deduction works properly
- Cost calculations include unit conversion
- Your restaurant POS system is ready to use!
