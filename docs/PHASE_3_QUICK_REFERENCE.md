# Phase 3 Quick Reference - Automatic Stock Deduction

**Status:** ✅ Complete  
**Date:** May 19, 2026

---

## 🎯 What Was Built

Automatic stock deduction system that:
- Deducts recipe ingredients when menu items are sold
- Converts units automatically (KG ↔ Gram, L ↔ ML, etc.)
- Tracks theoretical vs actual consumption
- Validates stock availability before sales

---

## 📊 Database Tables

### unit_conversions
Stores conversion rules between units
```
Key columns: from_unit_id, to_unit_id, conversion_factor, store_id
Example: 1 KG = 1000 Grams (factor: 1000)
```

### theoretical_consumption
Tracks expected vs actual ingredient usage
```
Key columns: product_id, store_id, date, theoretical_quantity, actual_quantity, variance
```

---

## 🔧 Core Services

### ConversionService
**Location:** `app/Services/UnitConversion/ConversionService.php`

```php
// Convert units
$service->convert(200, $gramId, $kgId); // Returns 0.2

// Create conversion
$service->createConversion($kgId, $gramId, 1000);

// Check if can convert
$service->canConvert($kgId, $gramId); // Returns true
```

### StockDeductionService
**Location:** `app/Services/Stock/StockDeductionService.php`

```php
// Deduct stock
$service->deductMenuItemStock(
    menuItemId: 1,
    quantity: 2,
    storeId: 1,
    userId: 1
);

// Check availability
$result = $service->checkMenuItemAvailability(1, 5, 1);

// Simulate (preview)
$preview = $service->simulateDeduction(1, 2, 1);
```

---

## 🌐 API Endpoints

### Unit Conversions

```bash
# List conversions
GET /api/unit-conversions?store_id=1

# Create conversion
POST /api/unit-conversions
{
  "from_unit_id": 1,
  "to_unit_id": 2,
  "conversion_factor": 1000
}

# Convert quantity
POST /api/unit-conversions/convert
{
  "quantity": 200,
  "from_unit_id": 2,
  "to_unit_id": 1
}

# Create standard conversions (KG→G, L→ML)
POST /api/unit-conversions/create-standard
```

### Stock Operations

```bash
# Check availability
POST /api/stock/check-availability
{
  "menu_item_id": 1,
  "quantity": 5,
  "store_id": 1
}

# Simulate deduction (preview)
POST /api/stock/simulate
{
  "menu_item_id": 1,
  "quantity": 2,
  "store_id": 1
}

# Actually deduct (manual test)
POST /api/stock/deduct
{
  "menu_item_id": 1,
  "quantity": 1,
  "store_id": 1,
  "user_id": 1
}

# Get consumption report
GET /api/stock/theoretical-consumption?store_id=1&date_from=2026-05-01

# Get variance report
GET /api/stock/variance-report?store_id=1&threshold=5
```

---

## 🔄 How It Works

### Automatic Deduction Flow

```
1. Order Created
   ↓
2. OrderSaleObserver triggered
   ↓
3. For each order item (if MenuItem):
   a. Get recipe → ingredients
   b. For each ingredient:
      - Calculate needed quantity (with waste %)
      - Convert units if necessary
      - Check stock availability
      - Deduct from inventory
      - Create stock movement
      - Update theoretical consumption
   ↓
4. Stock Updated ✅
```

### Example: Selling 2 Burgers

```
Burger Recipe (serves 1):
- 200g Beef (5% waste = 210g needed)
- 50g Lettuce (10% waste = 55g needed)
- 30ml Sauce

Selling 2 burgers:
1. Beef: 420g needed → Convert to 0.42kg → Deduct from stock
2. Lettuce: 110g needed → Convert to 0.11kg → Deduct from stock
3. Sauce: 60ml needed → Deduct from stock

Result: 
- StoreProducts updated
- 3 StockMovements created
- TheoreticalConsumption updated for each ingredient
```

---

## 🧪 Quick Test

### 1. Create Standard Conversions

```bash
curl -X POST http://localhost/api/unit-conversions/create-standard \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### 2. Check If Item Available

```bash
curl -X POST http://localhost/api/stock/check-availability \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "menu_item_id": 1,
    "quantity": 5,
    "store_id": 1
  }'
```

### 3. Create Order (Automatic Deduction)

```bash
# Create order through POS
# Stock will be automatically deducted via OrderSaleObserver
```

### 4. View Theoretical Consumption

```bash
curl -X GET "http://localhost/api/stock/theoretical-consumption?store_id=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 📝 Key Features

✅ **Automatic Deduction:** Stock auto-deducts on order creation  
✅ **Unit Conversion:** Flexible unit handling (KG↔G, L↔ML)  
✅ **Availability Check:** Prevent selling out-of-stock items  
✅ **Simulation:** Preview impact before deduction  
✅ **Consumption Tracking:** Monitor theoretical vs actual usage  
✅ **Variance Analysis:** Identify theft, waste, or portioning issues  
✅ **Audit Trail:** Full tracking in stock_movements table

---

## ⚙️ Configuration

### Create Conversion Rules

```php
// In seeder or setup script
$conversionService = app(\App\Services\UnitConversion\ConversionService::class);

// Weight conversions
$conversionService->createConversion($kgId, $gramId, 1000);
$conversionService->createConversion($gramId, $mgId, 1000);

// Volume conversions
$conversionService->createConversion($literId, $mlId, 1000);
$conversionService->createConversion($bottleId, $mlId, 750);

// Count conversions
$conversionService->createConversion($boxId, $pieceId, 24);
```

---

## 🐛 Troubleshooting

### "No conversion found" Error
**Solution:** Create conversion rule between the units
```bash
POST /api/unit-conversions
{
  "from_unit_id": 1,
  "to_unit_id": 2,
  "conversion_factor": 1000
}
```

### "Insufficient stock" Error
**Solution:** 
1. Check stock levels: `GET /api/store-products`
2. Receive inventory: `POST /api/purchases` (with approval)
3. Or adjust stock: `POST /api/store/adjustments`

### "Menu item has no recipe attached"
**Solution:** Assign recipe to menu item
```bash
PUT /api/menu-items/{id}
{
  "recipe_id": 1
}
```

### Stock Not Deducting Automatically
**Check:**
1. OrderSaleObserver is registered (check `app/Providers/AppServiceProvider.php`)
2. Order item product_type is `App\Models\MenuItem`
3. Menu item has valid recipe_id
4. Recipe has ingredients

---

## 📈 Next Steps

### Phase 4: Combo Meals
- Bundle multiple menu items
- Package pricing
- Automatic cost calculation

### Phase 5: Waste & Expiration
- Waste logging by category
- Batch tracking with expiration
- FEFO logic
- Expiration alerts

### Phase 6: Financial Reports
- P&L Report
- Food Cost Report
- Variance Analysis (using theoretical consumption)
- Recipe Profitability

---

## 📚 Related Documentation

- [Phase 3 Implementation Complete](./PHASE_3_IMPLEMENTATION_COMPLETE.md) - Detailed documentation
- [Phase 1 Quick Reference](./PHASE_1_QUICK_REFERENCE.md) - Recipe & Costing basics
- [Phase 2 Quick Reference](./PHASE_2_QUICK_REFERENCE.md) - Menu Management
- [Restaurant ERP Roadmap](./RESTAURANT_ERP_IMPLEMENTATION_ROADMAP.md) - Overall plan

---

**Last Updated:** May 19, 2026  
**Phase Status:** ✅ COMPLETE
