# Recipe Cost Calculation Fix - Unit Conversion

## 🐛 PROBLEM IDENTIFIED

When creating recipes and menu items, **cost calculations were completely wrong** (often 1000x too high) because the system didn't consider unit conversion between:

- Recipe ingredient units (e.g., Grams, Milliliters)
- Product storage units (e.g., Kilograms, Liters)

## 📊 EXAMPLE OF THE BUG

**Scenario:** Chicken Breast costs $8/Kilogram, recipe needs 250 Grams

### Before Fix ❌

```
Quantity: 250 (grams in recipe)
Cost: $8 (per kilogram in inventory)
Calculation: 250 × $8 = $2,000
Adding 10% waste: 275 × $8 = $2,200

Result: $2,200 for one salad! 💸
```

### After Fix ✅

```
Quantity: 250 grams
Convert to product unit: 250g ÷ 1000 = 0.25 kg
Cost: $8 per kilogram
Calculation: 0.25 kg × $8 = $2.00
Adding 10% waste: 0.275 kg × $8 = $2.20

Result: $2.20 for one salad ✓
```

## 🔍 YOUR ACTUAL DATA

### Recipe: Test Grilled Chicken Salad

#### Ingredient Costs - BEFORE FIX:

```
Chicken Breast (250g):  $2,200.00  ❌
Rice (150g):            $  225.00  ❌
Lettuce (100g):         $   92.00  ❌
Tomatoes (50g):         $   57.75  ❌
Olive Oil (20ml):       $  200.00  ❌
──────────────────────────────────
TOTAL:                  $2,774.75  ❌ WRONG!
```

#### Ingredient Costs - AFTER FIX:

```
Chicken Breast (250g):  $2.20  ✓ (0.275kg × $8/kg)
Rice (150g):            $0.23  ✓ (0.150kg × $1.5/kg)
Lettuce (100g):         $0.09  ✓ (0.115kg × $0.8/kg)
Tomatoes (50g):         $0.06  ✓ (0.0525kg × $1.1/kg)
Olive Oil (20ml):       $0.20  ✓ (0.020L × $10/L)
──────────────────────────────────
TOTAL:                  $2.77  ✓ CORRECT!
```

### Menu Item Analysis:

```
Menu Item Price:        $12.99
Recipe Cost:            $ 2.77
────────────────────────────
Profit per Item:        $10.22
Profit Margin:          78.64%
Food Cost %:            21.36% ✓ Realistic!
```

## 🔧 WHAT WAS FIXED

### File: `app/Models/RecipeIngredient.php`

**Changed Method:** `calculateCost()`

#### Key Changes:

1. **Get Product's Base Unit**

    ```php
    $productUnitId = $this->product->unit_id;
    $recipeUnitId = $this->unit_id;
    ```

2. **Convert Units if Different**

    ```php
    if ($productUnitId !== $recipeUnitId) {
        $conversionService = app(\App\Services\UnitConversion\ConversionService::class);
        $quantityInProductUnit = $conversionService->convert(
            $this->quantity,
            $recipeUnitId,
            $productUnitId,
            $this->recipe->store_id
        );
    }
    ```

3. **Calculate Cost with Converted Quantity**
    ```php
    $effectiveQuantity = $quantityInProductUnit * $wasteMultiplier;
    $totalCost = $effectiveQuantity * $costPerUnit;
    ```

## ✅ HOW TO VERIFY THE FIX

### Test 1: Check Current Costs

```bash
php tests/test_complete_cost_update.php
```

This will show you:

- Current ingredient costs (should be reasonable now)
- Recipe total cost
- Menu item cost
- Profit margins

### Test 2: Create a New Recipe

When you create a recipe through the API:

```json
POST /api/recipes
{
  "name": "New Dish",
  "yield_quantity": 1,
  "yield_unit_id": 1,
  "ingredients": [
    {
      "product_id": 1,
      "quantity": 250,
      "unit_id": 2,  // Grams (different from product unit)
      "waste_percentage": 10
    }
  ]
}
```

The costs will now be calculated correctly with unit conversion!

### Test 3: Update Existing Recipe

```bash
# Recalculate all recipe costs in your store
curl -X POST http://your-api/api/recipes/recalculate-all \
  -H "X-Store-ID: 1"
```

## 🎯 WHEN UNIT CONVERSION HAPPENS

### Scenarios Where Conversion is Needed:

1. **Weight Units:**
    - Product stored in: **Kilogram** (inventory)
    - Recipe uses: **Gram** (ingredient)
    - Conversion: 1 kg = 1000 g

2. **Volume Units:**
    - Product stored in: **Liter** (inventory)
    - Recipe uses: **Milliliter** (ingredient)
    - Conversion: 1 L = 1000 ml

3. **Same Unit Category:**
    - Product: Pound
    - Recipe: Ounce
    - Conversion: 1 lb = 16 oz

### When No Conversion Needed:

- Both use **same unit** → Direct calculation
- Example: Product stored in grams, recipe uses grams

## 🚨 IMPORTANT NOTES

### Required: Unit Conversion Table

For unit conversion to work, you need conversion records in the `unit_conversions` table:

```sql
-- Example conversions
INSERT INTO unit_conversions (from_unit_id, to_unit_id, conversion_factor) VALUES
(2, 1, 0.001),     -- Gram to Kilogram: 1g = 0.001kg
(1, 2, 1000),      -- Kilogram to Gram: 1kg = 1000g
(4, 3, 0.001),     -- Milliliter to Liter: 1ml = 0.001L
(3, 4, 1000);      -- Liter to Milliliter: 1L = 1000ml
```

### Fallback Behavior

If unit conversion fails (no conversion record found):

- ⚠️ A warning is logged
- 💾 System uses quantity as-is (without conversion)
- 🔄 Cost will be incorrect but won't break the system
- ✏️ **Fix:** Add the missing unit conversion record

## 📋 COST CALCULATION FLOW

### Complete Flow:

1. **Create Recipe** → RecipeService
2. **Add Ingredients** → RecipeIngredient records created
3. **Calculate Ingredient Cost** → `RecipeIngredient::calculateCost()`
    - Get product's unit and cost
    - Convert recipe quantity to product unit
    - Apply waste percentage
    - Multiply by cost per unit
4. **Calculate Recipe Total** → `Recipe::calculateTotalCost()`
    - Sum all ingredient costs
    - Calculate cost per serving
5. **Update Menu Item** → `MenuItem::updateCostFromRecipe()`
    - Set menu item cost = recipe total cost

## 🔄 UPDATING EXISTING RECIPES

If you have existing recipes with wrong costs:

### Option 1: Recalculate via API

```bash
POST /api/recipes/{id}/recalculate-cost
```

### Option 2: Recalculate All Recipes

```bash
POST /api/recipes/recalculate-all
Headers: X-Store-ID: {your_store_id}
```

### Option 3: Run Script

```bash
php tests/test_complete_cost_update.php
```

## 🎉 SUMMARY

**What was broken:**

- Recipe costs were 1000x too high
- No unit conversion between grams/kilograms
- Menu item costs were completely wrong
- Profit margins were impossible

**What's fixed:**

- ✅ Unit conversion added to cost calculation
- ✅ Proper conversion between weight units (g/kg)
- ✅ Proper conversion between volume units (ml/L)
- ✅ Correct costs for recipes and menu items
- ✅ Realistic profit margins

**Impact:**

- Recipe costs: $2,774.75 → $2.77 (1000x reduction)
- Food cost %: ~21,000% → 21.36% (realistic)
- Profit margin: -20,000% → 78.64% (profitable!)

**Your recipes now have accurate costing! 🎊**
