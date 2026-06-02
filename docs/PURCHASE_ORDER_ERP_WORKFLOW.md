# Purchase Order ERP Workflow - Bon de Commande & Bon de Livraison

## 📊 Current System Analysis

### What You Have Now

#### Current Workflow
```
1. Create Purchase Order (Brouillon)
   ↓
2. Approve Purchase
   ↓
3. Stock Updated Immediately
   ↓
4. Status = Completed
```

#### Current Tables Structure
**order_purchases:**
- `order_number` - Assigned on approval
- `status` - Single status (1=pending, 7=completed, 9=cancelled)
- `supplier_id`
- `store_id`
- `payment_term`
- `due_date`
- `discount`

**order_purchase_items:**
- `product_id`
- `order_id`
- `quantity` - Single quantity field (no distinction between ordered/received)
- `price`
- `total`

#### Current Limitations ⚠️
1. ❌ No separation between "Order Placed" and "Goods Received"
2. ❌ Stock updated immediately on approval (no receiving process)
3. ❌ Cannot track ordered vs received quantities
4. ❌ No partial delivery support
5. ❌ No delivery date tracking
6. ❌ No delivery note (Bon de Livraison) generation
7. ❌ Cannot handle multiple deliveries for one order

---

## 🎯 Target ERP Workflow (Bon de Commande + Bon de Livraison)

### Proper ERP Purchase Process

```
1. Create Bon de Commande (Purchase Order)
   - Order sent to supplier
   - Status: "Commandé" (Ordered)
   - Stock: NOT updated
   ↓
2. Receive Goods (Full or Partial)
   - Generate Bon de Livraison (Delivery Note)
   - Status: "En cours de réception" or "Reçu partiellement"
   - Stock: UPDATED for received quantities
   ↓
3. Complete Reception
   - All items received
   - Status: "Reçu complètement" (Fully Received)
   - Generate final BL
```

### Benefits of This Approach ✨
- ✅ Track what was ordered vs what was received
- ✅ Handle partial deliveries
- ✅ Multiple delivery notes per purchase order
- ✅ Accurate stock management
- ✅ Better supplier performance tracking
- ✅ Compliance with ERP best practices

---

## 🔧 Required Changes

### Option 1: Enhanced Single Table Approach (Simpler)

#### A. Add New Columns to `order_purchases`

```sql
ALTER TABLE order_purchases ADD COLUMN delivery_status VARCHAR(50) DEFAULT 'not_started';
-- not_started, in_progress, partially_received, fully_received

ALTER TABLE order_purchases ADD COLUMN ordered_date DATE;
ALTER TABLE order_purchases ADD COLUMN expected_delivery_date DATE;
ALTER TABLE order_purchases ADD COLUMN first_delivery_date DATE;
ALTER TABLE order_purchases ADD COLUMN last_delivery_date DATE;
```

#### B. Add New Columns to `order_purchase_items`

```sql
ALTER TABLE order_purchase_items ADD COLUMN ordered_quantity INT NOT NULL DEFAULT 0;
ALTER TABLE order_purchase_items ADD COLUMN received_quantity INT NOT NULL DEFAULT 0;
ALTER TABLE order_purchase_items ADD COLUMN remaining_quantity INT NOT NULL DEFAULT 0;

-- Rename existing quantity to ordered_quantity
ALTER TABLE order_purchase_items RENAME COLUMN quantity TO ordered_quantity;
```

#### C. Update Status Enum

Add new statuses to `EnumOrderStatue.php`:
```php
case ORDERED = 15;              // Bon de Commande créé
case RECEIVING = 16;            // En cours de réception
case PARTIALLY_RECEIVED = 17;   // Reçu partiellement
case FULLY_RECEIVED = 18;       // Reçu complètement
```

---

### Option 2: Separate Delivery Notes Table (More Robust)

#### A. Create New Table: `purchase_deliveries`

```php
Schema::create('purchase_deliveries', function (Blueprint $table) {
    $table->id();
    $table->string('delivery_number')->unique(); // BL-0001
    $table->foreignId('order_purchase_id')->constrained('order_purchases');
    $table->foreignId('store_id')->constrained('stores');
    $table->foreignId('supplier_id')->constrained('suppliers');
    $table->foreignId('received_by')->nullable()->constrained('users');
    
    $table->date('delivery_date');
    $table->string('supplier_delivery_note')->nullable(); // Supplier's BL number
    $table->string('transport_company')->nullable();
    $table->string('driver_name')->nullable();
    $table->string('vehicle_plate')->nullable();
    
    $table->text('delivery_note')->nullable();
    $table->text('quality_check_note')->nullable();
    $table->boolean('has_issues')->default(false);
    
    $table->enum('status', ['draft', 'validated', 'cancelled'])->default('draft');
    
    $table->timestamps();
});
```

#### B. Create New Table: `purchase_delivery_items`

```php
Schema::create('purchase_delivery_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('purchase_delivery_id')->constrained('purchase_deliveries');
    $table->foreignId('order_purchase_item_id')->constrained('order_purchase_items');
    $table->foreignId('product_id')->constrained('products');
    
    $table->integer('ordered_quantity');      // Reference from BC
    $table->integer('delivered_quantity');    // Actually received
    $table->integer('accepted_quantity');     // After quality check
    $table->integer('rejected_quantity')->default(0);
    
    $table->decimal('unit_price', 20, 2);
    $table->decimal('total_price', 20, 2);
    
    $table->text('rejection_reason')->nullable();
    $table->string('batch_number')->nullable();
    $table->date('expiry_date')->nullable();
    
    $table->timestamps();
});
```

---

## 📋 Recommended Implementation Approach

### I recommend **Option 2 (Separate Delivery Notes Table)** because:

1. ✅ **Flexibility**: Handle multiple deliveries per order
2. ✅ **Traceability**: Complete audit trail of all deliveries
3. ✅ **Quality Control**: Track rejected/accepted quantities
4. ✅ **Document Management**: Each BL is a separate document
5. ✅ **Reporting**: Better analytics on supplier performance
6. ✅ **Compliance**: Matches real-world business processes

---

## 🔄 New Workflow Implementation

### Step 1: Create Bon de Commande (Purchase Order)

```php
POST /api/order-purchases
{
    "supplier_id": 1,
    "reference": "BC-2026-001",
    "expected_delivery_date": "2026-06-10",
    "payment_term": 2,
    "status": 15,  // ORDERED
    "details": [
        {
            "product_id": 1,
            "ordered_quantity": 100,
            "price": 10.50,
            "total": 1050
        }
    ]
}
```

**What happens:**
- ✅ BC created with status "Commandé"
- ✅ Order number generated (BC-0001)
- ❌ Stock NOT updated yet
- ✅ Can print Bon de Commande PDF

### Step 2: Receive Goods (Create Bon de Livraison)

```php
POST /api/purchase-deliveries
{
    "order_purchase_id": 1,
    "delivery_date": "2026-06-10",
    "supplier_delivery_note": "SUPP-BL-12345",
    "received_by": 2,
    "items": [
        {
            "order_purchase_item_id": 1,
            "product_id": 1,
            "delivered_quantity": 50,    // Partial delivery
            "accepted_quantity": 50,
            "rejected_quantity": 0
        }
    ]
}
```

**What happens:**
- ✅ BL created with number (BL-0001)
- ✅ Stock updated with **accepted_quantity** (50)
- ✅ Purchase order status → "PARTIALLY_RECEIVED"
- ✅ Can print Bon de Livraison PDF
- ✅ Stock movement created (type: delivery, qty: 50)

### Step 3: Receive Remaining Items

```php
POST /api/purchase-deliveries
{
    "order_purchase_id": 1,
    "delivery_date": "2026-06-15",
    "items": [
        {
            "order_purchase_item_id": 1,
            "product_id": 1,
            "delivered_quantity": 50,    // Remaining quantity
            "accepted_quantity": 48,     // 2 rejected
            "rejected_quantity": 2,
            "rejection_reason": "Damaged packaging"
        }
    ]
}
```

**What happens:**
- ✅ BL-0002 created
- ✅ Stock updated (+48)
- ✅ Purchase order status → "FULLY_RECEIVED"
- ✅ Rejection logged for supplier review
- ✅ Can claim rejected items with supplier

---

## 🗂️ New Models & Services Needed

### Models to Create

1. **PurchaseDelivery.php**
   ```php
   class PurchaseDelivery extends BaseModel
   {
       public function orderPurchase()
       public function deliveryItems()
       public function receivedBy()
       public function store()
       public function supplier()
   }
   ```

2. **PurchaseDeliveryItem.php**
   ```php
   class PurchaseDeliveryItem extends BaseModel
   {
       public function purchaseDelivery()
       public function orderPurchaseItem()
       public function product()
   }
   ```

### Services to Create/Update

1. **PurchaseDeliveryService.php** (NEW)
   - `createDelivery(array $data)`
   - `validateDelivery(int $deliveryId)`
   - `cancelDelivery(int $deliveryId)`
   - `checkOrderCompleteness(int $orderId)`

2. **PurchaseService.php** (UPDATE)
   - Keep existing `create()` for BC creation
   - Remove direct stock update from `approvePurchase()`
   - Add status change to "ORDERED"
   - Stock update only through delivery notes

3. **PurchaseDocumentService.php** (NEW)
   - `generateBonDeCommandePDF(int $orderId)`
   - `generateBonDeLivraisonPDF(int $deliveryId)`

### Controllers to Create/Update

1. **PurchaseDeliveryController.php** (NEW)
   - `index()` - List all deliveries
   - `store()` - Create new delivery
   - `show($id)` - View delivery details
   - `validate($id)` - Validate delivery
   - `getPDF($id)` - Download BL PDF

---

## 🎨 Frontend Changes Needed

### Purchase Order List Page
- Show "Bon de Commande" status
- Display ordered vs received quantities
- Action buttons:
  - ✅ "Créer Bon de Livraison" (if not fully received)
  - 📄 "Imprimer BC"
  - 📋 "Voir Livraisons"

### New Delivery Notes Page
- List all BL for a purchase order
- Show delivery dates, quantities
- Track quality issues
- Print individual BL

### Enhanced Purchase Order Details
```
Bon de Commande: BC-0001
Statut: Reçu Partiellement
Fournisseur: XYZ Supplier
Date Commande: 2026-06-01
Date Livraison Prévue: 2026-06-10

Articles:
┌────────────────┬─────────┬────────┬─────────┬──────────┐
│ Produit        │ Commandé│ Reçu   │ Restant │ Statut   │
├────────────────┼─────────┼────────┼─────────┼──────────┤
│ Poulet         │ 100 kg  │ 50 kg  │ 50 kg   │ Partiel  │
│ Riz            │ 200 kg  │ 200 kg │ 0 kg    │ Complet  │
└────────────────┴─────────┴────────┴─────────┴──────────┘

Bons de Livraison:
- BL-0001 (2026-06-10) - 50 kg Poulet, 200 kg Riz
- En attente: 50 kg Poulet
```

---

## 📝 Step-by-Step Migration Guide

### Phase 1: Database Changes
1. Create migrations for new tables
2. Add new status enums
3. Update existing data (set ordered_quantity = quantity)

### Phase 2: Backend Implementation
1. Create new models (PurchaseDelivery, PurchaseDeliveryItem)
2. Create PurchaseDeliveryService
3. Update PurchaseService to not update stock directly
4. Create API endpoints for deliveries
5. Add PDF generation for BC and BL

### Phase 3: Frontend Updates
1. Update purchase order list to show delivery status
2. Create delivery note creation form
3. Add delivery notes list page
4. Update purchase details page

### Phase 4: Testing
1. Test creating BC without stock update
2. Test partial delivery reception
3. Test multiple deliveries per order
4. Test quality rejection workflow
5. Test PDF generation

---

## 📊 API Endpoints Overview

### Existing (Keep)
```
GET    /api/order-purchases          # List purchase orders
POST   /api/order-purchases          # Create BC (no stock update)
GET    /api/order-purchases/{id}     # View BC details
DELETE /api/order-purchases/{id}     # Cancel BC
```

### New Endpoints Needed
```
POST   /api/order-purchases/{id}/deliveries     # Create BL
GET    /api/order-purchases/{id}/deliveries     # List BLs for BC
GET    /api/purchase-deliveries/{id}            # View BL details
POST   /api/purchase-deliveries/{id}/validate   # Validate BL (update stock)
DELETE /api/purchase-deliveries/{id}            # Cancel BL
GET    /api/purchase-deliveries/{id}/pdf        # Download BL PDF
GET    /api/order-purchases/{id}/pdf            # Download BC PDF
```

---

## 🎯 Quick Decision Matrix

| Feature | Current System | Option 1 (Simple) | Option 2 (Robust) |
|---------|---------------|-------------------|-------------------|
| Partial Deliveries | ❌ | ✅ Limited | ✅ Full Support |
| Multiple BL per BC | ❌ | ❌ | ✅ |
| Quality Control | ❌ | ❌ | ✅ |
| Separate BL Document | ❌ | ⚠️ Simulated | ✅ |
| Delivery Tracking | ❌ | ⚠️ Basic | ✅ Complete |
| Implementation Time | - | 2-3 days | 5-7 days |
| Database Changes | - | Small | Moderate |
| Scalability | - | ⚠️ Limited | ✅ Excellent |

---

## 💡 Recommendation

**Implement Option 2 (Separate Delivery Notes Table)** for these reasons:

1. **Future-Proof**: Handles complex scenarios as business grows
2. **Compliance**: Matches real ERP workflows
3. **Audit Trail**: Complete history of all deliveries
4. **Flexibility**: Easy to add features (batch tracking, expiry dates, etc.)
5. **Best Practice**: Industry standard approach

The additional implementation time (5-7 days vs 2-3 days) is worth the long-term benefits.

---

## 🚀 Next Steps

1. **Decision**: Choose Option 1 or Option 2
2. **Planning**: Create detailed migration plan
3. **Database**: Create and run migrations
4. **Backend**: Implement services and controllers
5. **Frontend**: Update UI components
6. **Testing**: Comprehensive testing
7. **Deployment**: Roll out to production

---

## 📞 Questions to Answer

Before implementation, clarify:

1. Can one purchase order have multiple deliveries? → **Recommended: YES**
2. Do you need to track rejected/damaged items? → **Recommended: YES**
3. Do you need batch/lot number tracking? → **Optional**
4. Do you need expiry date tracking? → **For perishables: YES**
5. Should stock update on BL creation or validation? → **Recommended: On validation**
6. Who can create/validate BL? → **Define role permissions**

---

*Document created: 2026-06-02*
*Author: GitHub Copilot*
