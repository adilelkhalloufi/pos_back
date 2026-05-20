# Units & Conversions Seeder - Quick Guide

## 🎯 Purpose

This seeder creates all standard units and conversions needed for restaurant inventory management. Run this **once** before creating products and recipes.

---

## 🚀 How to Run

```bash
php artisan db:seed --class=UnitsAndConversionsSeeder
```

**Or run with full database seed:**
```bash
php artisan migrate:fresh --seed
```

---

## 📦 What Gets Created

### Units Created (27 total)

#### Weight Units (6)
- Kilogram (kg)
- Gram (g)
- Milligram (mg)
- Pound (lb)
- Ounce (oz)
- Ton (t)

#### Volume Units (8)
- Liter (L)
- Milliliter (ml)
- Gallon (gal)
- Quart (qt)
- Pint (pt)
- Cup (cup)
- Tablespoon (tbsp)
- Teaspoon (tsp)

#### Count Units (10)
- Piece (pc)
- Dozen (doz)
- Box (box)
- Case (case)
- Crate (crate)
- Bag (bag)
- Bottle (btl)
- Can (can)
- Jar (jar)
- Pack (pack)

#### Serving Units (3)
- Portion
- Serving
- Plate

---

## 🔄 Conversions Created (70+ total)

### Weight Conversions
```
✓ 1 kg = 1000 g
✓ 1 kg = 1,000,000 mg
✓ 1 kg = 2.20462 lb
✓ 1 kg = 35.274 oz
✓ 1 lb = 453.592 g
✓ 1 oz = 28.3495 g
✓ 1 ton = 1000 kg
... and reverse conversions
```

### Volume Conversions
```
✓ 1 L = 1000 ml
✓ 1 L = 0.264172 gal
✓ 1 gal = 3.78541 L
✓ 1 cup = 236.588 ml
✓ 1 tbsp = 14.7868 ml
✓ 1 tsp = 4.92892 ml
... and reverse conversions
```

### Count Conversions
```
✓ 1 dozen = 12 pieces
✓ 1 box = 12 pieces (default)
✓ 1 case = 24 pieces (default)
✓ 1 bottle = 750 ml (standard wine bottle)
✓ 1 can = 330 ml (standard soda can)
```

---

## ✅ Verify Seeder Success

```sql
-- Check units count
SELECT COUNT(*) FROM units;
-- Expected: 27

-- Check conversions count
SELECT COUNT(*) FROM unit_conversions;
-- Expected: 70+

-- Test a conversion
SELECT * FROM unit_conversions 
WHERE from_unit_id = 1 AND to_unit_id = 2;
-- Expected: 1 kg = 1000 g
```

---

## 🎓 Usage Examples

### After running seeder, you can:

**Create products with units:**
```json
POST /api/products
{
  "name": "Chicken Breast",
  "unit_id": 1  // Kilogram
}
```

**Create recipes with different units:**
```json
POST /api/recipes
{
  "name": "Grilled Chicken",
  "ingredients": [
    {
      "product_id": 1,
      "quantity": 250,
      "unit_id": 2  // Grams (auto-converts to KG)
    }
  ]
}
```

**System automatically converts:**
- Recipe needs 250g chicken
- Inventory tracks in KG
- System converts: 250g → 0.25 KG
- Stock deducts 0.25 from inventory

---

## 🔧 Customization

### Add Store-Specific Conversions

You can add custom conversions for specific stores:

```php
DB::table('unit_conversions')->insert([
    'from_unit_id' => 17,  // Box
    'to_unit_id' => 15,    // Piece
    'conversion_factor' => 30,  // Your box has 30 pieces
    'store_id' => 1,  // Store-specific override
    'created_at' => now(),
    'updated_at' => now(),
]);
```

Store-specific conversions override global conversions.

---

## 💡 Best Practices

1. **Run once during setup** - Don't run repeatedly
2. **Backup first** if running in production
3. **Test conversions** before creating recipes
4. **Add custom conversions** as needed for your business
5. **Document custom units** for your team

---

## 🐛 Troubleshooting

### Issue: "Duplicate entry" error
**Solution:** Units/conversions already exist. Drop and recreate:
```bash
php artisan migrate:fresh --seed
```

### Issue: Conversions not working in recipes
**Solution:** Verify conversion exists:
```sql
SELECT * FROM unit_conversions 
WHERE from_unit_id = X AND to_unit_id = Y;
```

### Issue: Need different conversion factor
**Solution:** Update existing conversion:
```sql
UPDATE unit_conversions 
SET conversion_factor = NEW_VALUE 
WHERE from_unit_id = X AND to_unit_id = Y;
```

---

## 📚 Related Files

- Migration: `2026_03_04_000001_create_units_table.php`
- Migration: `2026_05_19_150550_create_unit_conversions_table.php`
- Service: `app/Services/UnitConversion/ConversionService.php`
- Docs: `docs/COMPLETE_WORKFLOW_GUIDE.md`
- Docs: `docs/QUICK_START_CHECKLIST.md`

---

**Last Updated:** May 20, 2026  
**Status:** Production Ready ✅  
**Total Units:** 27  
**Total Conversions:** 70+
