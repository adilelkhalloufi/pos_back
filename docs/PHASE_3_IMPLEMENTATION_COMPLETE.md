# Phase 3 Implementation Complete ✅

**Implementation Date:** May 19, 2026  
**Duration:** 1 session  
**Status:** ✅ **COMPLETE**

---

## Overview

Phase 3 implements automatic stock deduction for recipe ingredients when menu items are sold through the POS system. This includes:

- Unit conversion system for flexible unit management
- Automatic ingredient deduction from inventory
- Theoretical consumption tracking
- Variance analysis capabilities
- Stock availability checking

---

## Database Changes

### New Tables

#### 1. unit_conversions
```sql
unit_conversions
├── id (PK)
├── from_unit_id FK(units)
├── to_unit_id FK(units)
├── conversion_factor DECIMAL(12,6)
├── store_id FK(stores) NULLABLE
├── created_at
└── updated_at

Indexes:
- UNIQUE(from_unit_id, to_unit_id, store_id)
- INDEX(from_unit_id, to_unit_id)
- INDEX(store_id)
```

**Purpose:** Stores conversion rules between units (e.g., 1 KG = 1000 Grams)

#### 2. theoretical_consumption
```sql
theoretical_consumption
├── id (PK)
├── product_id FK(products)
├── store_id FK(stores)
├── date DATE
├── theoretical_quantity DECIMAL(14,4)
├── actual_quantity DECIMAL(14,4)
├── variance DECIMAL(14,4)
├── variance_percentage DECIMAL(5,2)
├── created_at
└── updated_at

Indexes:
- UNIQUE(product_id, store_id, date)
- INDEX(store_id, date)
- INDEX(date)
```

**Purpose:** Tracks expected vs actual ingredient consumption for variance analysis

---

## New Models

### 1. UnitConversion
**Location:** `app/Models/UnitConversion.php`

**Relationships:**
- `fromUnit()` - Source unit
- `toUnit()` - Target unit
- `store()` - Optional store-specific conversion

**Key Features:**
- Supports both global and store-specific conversions
- Unique constraint prevents duplicate conversion rules

### 2. TheoreticalConsumption
**Location:** `app/Models/TheoreticalConsumption.php`

**Relationships:**
- `product()` - The ingredient being tracked
- `store()` - The store

**Key Methods:**
- `calculateVariance()` - Computes variance and variance percentage

---

## New Services

### 1. ConversionService
**Location:** `app/Services/UnitConversion/ConversionService.php`

**Purpose:** Handles all unit conversion logic

**Key Methods:**

```php
// Convert quantity from one unit to another
convert(float $quantity, int $fromUnitId, int $toUnitId, ?int $storeId = null): float

// Create or update a conversion rule
createConversion(int $fromUnitId, int $toUnitId, float $conversionFactor, ?int $storeId = null): UnitConversion

// Delete a conversion rule
deleteConversion(int $conversionId): bool

// Check if conversion is possible
canConvert(int $fromUnitId, int $toUnitId, ?int $storeId = null): bool

// Create standard conversions (KG→G, L→ML, etc.)
createStandardConversions(?int $storeId = null): array
```

**Features:**
- Bidirectional conversion support (A→B or B→A)
- Cache optimization (3600s TTL)
- Store-specific overrides
- Validation and error handling

**Example Usage:**
```php
$service = app(\App\Services\UnitConversion\ConversionService::class);

// Create conversion: 1 KG = 1000 Grams
$conversion = $service->createConversion(
    fromUnitId: $kgUnit->id,
    toUnitId: $gramUnit->id,
    conversionFactor: 1000,
    storeId: null // Global conversion
);

// Convert 200 grams to kilograms
$kg = $service->convert(200, $gramUnit->id, $kgUnit->id); // Returns 0.2
```

### 2. StockDeductionService
**Location:** `app/Services/Stock/StockDeductionService.php`

**Purpose:** Automatically deducts recipe ingredients from inventory when menu items are sold

**Key Methods:**

```php
// Deduct stock for a menu item sale
deductMenuItemStock(
    int $menuItemId,
    float $quantity,
    int $storeId,
    int $userId,
    $orderSaleId = null
): array

// Check if menu item can be sold (sufficient stock)
checkMenuItemAvailability(int $menuItemId, float $quantity, int $storeId): array

// Simulate deduction without actually deducting (preview)
simulateDeduction(int $menuItemId, float $quantity, int $storeId): array
```

**Business Logic Flow:**

```
Menu Item Sold (Quantity: 2)
       ↓
Get Recipe → Find all recipe ingredients
       ↓
For each ingredient:
  1. Calculate quantity needed × multiplier × (1 + waste%)
  2. Check if unit conversion needed
     (Recipe: 200g, Stock: KG → Convert: 0.2kg)
  3. Validate sufficient stock
  4. Deduct from StoreProducts.stock
  5. Create StockMovement (type: sale, direction: out)
  6. Update TheoreticalConsumption
       ↓
Stock Updated ✅
```

**Example Usage:**
```php
$service = app(\App\Services\Stock\StockDeductionService::class);

// Check if can sell 5 burgers
$availability = $service->checkMenuItemAvailability(
    menuItemId: $burgerId,
    quantity: 5,
    storeId: 1
);

if ($availability['available']) {
    // Deduct stock
    $result = $service->deductMenuItemStock(
        menuItemId: $burgerId,
        quantity: 5,
        storeId: 1,
        userId: auth()->id()
    );
}
```

---

## Integration with Existing Systems

### OrderSaleObserver Enhancement
**Location:** `app/Observers/OrderSaleObserver.php`

**Changes:**
- Added `StockDeductionService` dependency
- New method: `deductStockForOrder()`
- Triggers automatic stock deduction on order creation

**Logic:**
```php
When OrderSale created:
  1. Load all order items
  2. For each order item:
     - If product_type == MenuItem
     - Call deductMenuItemStock()
     - Log success/failure
  3. Continue even if individual items fail (logged for review)
```

**Error Handling:**
- Logs errors but doesn't fail order creation
- Allows business to continue operation
- Provides audit trail for investigation

---

## API Endpoints

### Unit Conversions

#### GET `/api/unit-conversions`
Get all unit conversions for a store

**Query Parameters:**
- `store_id` - Store ID (optional, defaults to current store)
- `include_global` - Include global conversions (boolean, default: true)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "from_unit_id": 1,
      "to_unit_id": 2,
      "conversion_factor": 1000,
      "store_id": null,
      "from_unit": { "id": 1, "name": "Kilogram", "symbol": "kg" },
      "to_unit": { "id": 2, "name": "Gram", "symbol": "g" }
    }
  ]
}
```

#### POST `/api/unit-conversions`
Create a new conversion rule

**Request Body:**
```json
{
  "from_unit_id": 1,
  "to_unit_id": 2,
  "conversion_factor": 1000,
  "store_id": null
}
```

#### PUT `/api/unit-conversions/{id}`
Update conversion factor

**Request Body:**
```json
{
  "conversion_factor": 1000
}
```

#### DELETE `/api/unit-conversions/{id}`
Delete a conversion rule

#### POST `/api/unit-conversions/convert`
Convert a quantity

**Request Body:**
```json
{
  "quantity": 200,
  "from_unit_id": 2,
  "to_unit_id": 1,
  "store_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "original_quantity": 200,
    "from_unit_id": 2,
    "to_unit_id": 1,
    "converted_quantity": 0.2
  }
}
```

#### POST `/api/unit-conversions/create-standard`
Create standard conversions (KG→G, L→ML, etc.)

**Request Body:**
```json
{
  "store_id": null
}
```

### Stock Deduction

#### POST `/api/stock/deduct`
Manually deduct stock for a menu item (for testing)

**Request Body:**
```json
{
  "menu_item_id": 1,
  "quantity": 2,
  "store_id": 1,
  "user_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "message": "Stock deducted successfully",
  "data": {
    "menu_item": "Classic Burger",
    "quantity_sold": 2,
    "recipe": "Burger Recipe",
    "deductions": [
      {
        "product_id": 5,
        "product_name": "Beef Ground",
        "quantity_deducted": 0.4,
        "unit_cost": 10.50,
        "total_cost": 4.20,
        "remaining_stock": 49.6,
        "stock_movement_id": 123
      }
    ]
  }
}
```

#### POST `/api/stock/check-availability`
Check if menu item can be sold

**Request Body:**
```json
{
  "menu_item_id": 1,
  "quantity": 10,
  "store_id": 1
}
```

**Response (Available):**
```json
{
  "success": true,
  "data": {
    "available": true,
    "missing_items": []
  }
}
```

**Response (Insufficient Stock):**
```json
{
  "success": true,
  "data": {
    "available": false,
    "missing_items": [
      {
        "product_name": "Beef Ground",
        "required": 2.0,
        "available": 0.5,
        "shortage": 1.5
      }
    ]
  }
}
```

#### POST `/api/stock/simulate`
Simulate deduction without actually deducting

**Request Body:**
```json
{
  "menu_item_id": 1,
  "quantity": 5,
  "store_id": 1
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "menu_item": "Classic Burger",
    "quantity": 5,
    "recipe": "Burger Recipe",
    "ingredients": [
      {
        "product_name": "Beef Ground",
        "quantity_needed": 1000,
        "quantity_to_deduct": 1.0,
        "current_stock": 50,
        "remaining_stock": 49,
        "unit_cost": 10.50,
        "total_cost": 10.50,
        "sufficient": true
      }
    ],
    "total_cost": 10.50
  }
}
```

#### GET `/api/stock/theoretical-consumption`
Get theoretical consumption records

**Query Parameters:**
- `store_id` - Store ID
- `date_from` - Start date (YYYY-MM-DD)
- `date_to` - End date (YYYY-MM-DD)

**Response:**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "product_id": 5,
        "store_id": 1,
        "date": "2026-05-19",
        "theoretical_quantity": 10.0,
        "actual_quantity": 11.5,
        "variance": 1.5,
        "variance_percentage": 15.0,
        "product": {
          "id": 5,
          "name": "Beef Ground"
        }
      }
    ],
    "per_page": 50,
    "total": 1
  }
}
```

#### GET `/api/stock/variance-report`
Get high variance items

**Query Parameters:**
- `store_id` - Store ID
- `threshold` - Variance percentage threshold (default: 5)
- `date_from` - Start date
- `date_to` - End date

**Response:**
```json
{
  "success": true,
  "data": {
    "threshold": 5,
    "high_variance_items": [
      {
        "product_id": 5,
        "product_name": "Beef Ground",
        "variance_percentage": 15.0,
        "theoretical_quantity": 10.0,
        "actual_quantity": 11.5,
        "variance": 1.5
      }
    ],
    "total_items": 1
  }
}
```

---

## Testing & Verification

### Verification Script

Create a test file: `tests/phase3_verification.php`

```php
<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\UnitConversion\ConversionService;
use App\Services\Stock\StockDeductionService;
use App\Models\Unit;
use App\Models\MenuItem;

echo "=== Phase 3 Verification ===\n\n";

// Test 1: Unit Conversion
echo "Test 1: Create Unit Conversion (KG → Gram)\n";
$conversionService = app(ConversionService::class);

$kg = Unit::where('symbol', 'kg')->first();
$g = Unit::where('symbol', 'g')->first();

if ($kg && $g) {
    $conversion = $conversionService->createConversion($kg->id, $g->id, 1000);
    echo "✅ Conversion created: 1 KG = 1000 Grams\n";
    
    // Test conversion
    $result = $conversionService->convert(0.5, $kg->id, $g->id);
    echo "✅ Conversion test: 0.5 KG = {$result} Grams\n";
} else {
    echo "❌ KG or Gram units not found\n";
}

echo "\n";

// Test 2: Stock Availability Check
echo "Test 2: Check Menu Item Availability\n";
$stockDeductionService = app(StockDeductionService::class);

$menuItem = MenuItem::with('recipe')->first();

if ($menuItem && $menuItem->recipe_id) {
    $availability = $stockDeductionService->checkMenuItemAvailability(
        $menuItem->id,
        1,
        $menuItem->store_id
    );
    
    if ($availability['available']) {
        echo "✅ Menu item '{$menuItem->name}' is available\n";
    } else {
        echo "⚠️ Menu item '{$menuItem->name}' has insufficient stock:\n";
        foreach ($availability['missing_items'] as $missing) {
            echo "  - {$missing['product_name']}: shortage of {$missing['shortage']}\n";
        }
    }
} else {
    echo "❌ No menu items with recipes found\n";
}

echo "\n";

// Test 3: Simulate Stock Deduction
echo "Test 3: Simulate Stock Deduction\n";

if ($menuItem && $menuItem->recipe_id) {
    $simulation = $stockDeductionService->simulateDeduction(
        $menuItem->id,
        2,
        $menuItem->store_id
    );
    
    echo "✅ Simulation for 2× {$simulation['menu_item']}:\n";
    echo "  Total Cost: \${$simulation['total_cost']}\n";
    echo "  Ingredients:\n";
    
    foreach ($simulation['ingredients'] as $ingredient) {
        $status = $ingredient['sufficient'] ? '✅' : '❌';
        echo "    {$status} {$ingredient['product_name']}: {$ingredient['quantity_to_deduct']} units\n";
    }
}

echo "\n=== Verification Complete ===\n";
```

### Manual Testing Steps

1. **Create Standard Unit Conversions:**
```bash
curl -X POST http://localhost/api/unit-conversions/create-standard \
  -H "Authorization: Bearer YOUR_TOKEN"
```

2. **Check Menu Item Availability:**
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

3. **Simulate Stock Deduction:**
```bash
curl -X POST http://localhost/api/stock/simulate \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "menu_item_id": 1,
    "quantity": 2,
    "store_id": 1
  }'
```

4. **Actually Deduct Stock:**
```bash
curl -X POST http://localhost/api/stock/deduct \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "menu_item_id": 1,
    "quantity": 1,
    "store_id": 1,
    "user_id": 1
  }'
```

5. **Check Theoretical Consumption:**
```bash
curl -X GET "http://localhost/api/stock/theoretical-consumption?store_id=1&date_from=2026-05-01" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Key Features Enabled

✅ **Automatic Stock Deduction**
- Recipe ingredients automatically deducted when menu items sold
- Works seamlessly with POS orders

✅ **Unit Conversion System**
- Flexible unit management (KG ↔ Gram, L ↔ ML, etc.)
- Store-specific conversion overrides
- Bidirectional conversion support

✅ **Theoretical Consumption Tracking**
- Tracks expected vs actual ingredient usage
- Daily aggregation by product and store
- Foundation for variance analysis

✅ **Stock Availability Checking**
- Real-time availability checks before order placement
- Prevents selling items with insufficient stock
- Detailed shortage reporting

✅ **Simulation & Preview**
- Preview stock impact before actual deduction
- Cost calculation for informed decision making

✅ **Audit Trail**
- All deductions logged in stock_movements
- Links to original order for traceability
- User tracking for accountability

---

## What's Next

### Phase 4: Combo Meals (1 week)
- Bundle multiple menu items at package price
- Automatic cost calculation for combos
- Stock deduction for all combo components

### Phase 5: Waste & Expiration Tracking (2 weeks)
- Log waste by category (spoilage, prep waste, etc.)
- Batch/lot tracking with expiration dates
- FEFO (First Expired First Out) logic
- Expiration alerts

### Phase 6: Financial Reporting (3 weeks)
- P&L Report
- Food Cost Report
- Recipe Profitability Analysis
- Inventory Valuation
- Variance Reports (using theoretical consumption)
- Waste Cost Analysis

---

## Technical Notes

### Performance Considerations

1. **Caching:**
   - Unit conversions cached for 1 hour
   - Cache cleared on conversion updates
   - Reduces database queries

2. **Database Indexes:**
   - Composite unique indexes prevent duplicates
   - Performance indexes on foreign keys
   - Date indexes for time-based queries

3. **Error Handling:**
   - Stock deduction failures logged but don't break orders
   - Graceful degradation strategy
   - Comprehensive error messages for debugging

### Security Considerations

1. **Authorization:**
   - All endpoints require authentication
   - Store-level access control via middleware
   - User tracking on all stock movements

2. **Validation:**
   - Input validation on all endpoints
   - Foreign key constraints enforce data integrity
   - Positive quantity validation

---

## Database Migrations Applied

```bash
2026_05_19_150550_create_unit_conversions_table.php
2026_05_19_151630_create_theoretical_consumption_table.php
```

## Files Created/Modified

### Created:
- `database/migrations/2026_05_19_150550_create_unit_conversions_table.php`
- `database/migrations/2026_05_19_151630_create_theoretical_consumption_table.php`
- `app/Models/UnitConversion.php`
- `app/Models/TheoreticalConsumption.php`
- `app/Services/UnitConversion/ConversionService.php`
- `app/Services/Stock/StockDeductionService.php`
- `app/Http/Controllers/Api/UnitConversionController.php`
- `app/Http/Controllers/Api/StockDeductionController.php`

### Modified:
- `app/Observers/OrderSaleObserver.php` - Added stock deduction trigger
- `routes/api.php` - Added 11 new API endpoints

---

## Success Metrics

✅ Migrations applied successfully  
✅ Models created with proper relationships  
✅ Services implemented with full business logic  
✅ API endpoints created and documented  
✅ Observer integration complete  
✅ Routes registered  

**Phase 3 Status: ✅ COMPLETE**

---

**Implementation Date:** May 19, 2026  
**Next Phase:** Phase 4 - Combo Meals
