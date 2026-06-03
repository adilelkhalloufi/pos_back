# Purchase Order ERP Workflow - Implementation Guide

## ✅ What Has Been Implemented

### Database Structure

#### New Tables Created:

1. **purchase_deliveries** - Stores delivery notes (Bon de Livraison)
    - `delivery_number` - Unique delivery number (BL-0001)
    - `order_purchase_id` - Link to purchase order
    - `delivery_date` - Date of delivery
    - `received_by` - User who received the delivery
    - `supplier_delivery_note` - Supplier's BL number
    - `transport_company`, `driver_name`, `vehicle_plate` - Logistics info
    - `delivery_note`, `quality_check_note` - Notes
    - `has_issues` - Quality issue flag
    - `status` - draft, validated, cancelled

2. **purchase_delivery_items** - Items in each delivery
    - `ordered_quantity` - Quantity in purchase order
    - `delivered_quantity` - Quantity actually delivered
    - `accepted_quantity` - Quantity accepted after quality check
    - `rejected_quantity` - Quantity rejected
    - `unit_price`, `total_price` - Pricing
    - `rejection_reason` - Why items were rejected
    - `batch_number`, `expiry_date` - Traceability

#### Enhanced Existing Tables:

1. **order_purchases** - Added columns:
    - `delivery_status` - not_started, partially_received, fully_received
    - `ordered_date`, `expected_delivery_date`
    - `first_delivery_date`, `last_delivery_date`

2. **order_purchase_items** - Added columns:
    - `received_quantity` - Total received so far
    - `remaining_quantity` - Still waiting to receive

### New Status Enums

Added to `EnumOrderStatue`:

- `ORDERED = 15` - Bon de Commande créé
- `RECEIVING = 16` - En cours de réception
- `PARTIALLY_RECEIVED = 17` - Reçu partiellement
- `FULLY_RECEIVED = 18` - Reçu complètement

### Models & Repositories

- ✅ `PurchaseDelivery` model
- ✅ `PurchaseDeliveryItem` model
- ✅ Updated `OrderPurchase` model with new relationships
- ✅ Updated `OrderPurchaseItems` model with new columns
- ✅ `PurchaseDeliveryRepository`
- ✅ `PurchaseDeliveryItemRepository`

### Services

- ✅ `PurchaseDeliveryService` with methods:
    - `createDelivery()` - Create new delivery note
    - `validateDelivery()` - Validate and update stock
    - `cancelDelivery()` - Cancel a delivery
    - `checkOrderCompleteness()` - Update purchase order status
    - `getDeliveriesForPurchaseOrder()` - List all deliveries

### Controllers & Resources

- ✅ `PurchaseDeliveryController`
- ✅ `PurchaseDeliveryResource`
- ✅ `PurchaseDeliveryItemResource`

### API Endpoints

```
GET    /api/purchases/{purchaseOrderId}/deliveries    # List deliveries for a purchase order
POST   /api/purchase-deliveries                       # Create new delivery
GET    /api/purchase-deliveries/{id}                  # View delivery details
POST   /api/purchase-deliveries/{id}/validate         # Validate delivery & update stock
POST   /api/purchase-deliveries/{id}/cancel           # Cancel delivery
```

---

## 🚀 How to Use the New Workflow

### Step 1: Create Purchase Order (Bon de Commande)

**Endpoint:** `POST /api/purchases`

```json
{
    "supplier_id": 1,
    "reference": "BC-2026-001",
    "expected_delivery_date": "2026-06-10",
    "payment_term": 2,
    "paid_method_id": 1,
    "public_note": "Order for June",
    "details": [
        {
            "product_id": 1,
            "name": "Chicken",
            "quantity": 100,
            "price": 10.5,
            "total": 1050,
            "store_id": 1
        }
    ]
}
```

**What happens:**

- ✅ Purchase order created with status `PENDING`
- ✅ Order number = "Brouillon" (draft)
- ❌ Stock NOT updated yet
- ✅ Can be approved later

**Note:** The old workflow still works! You can still approve purchase orders directly with `PUT /api/purchases/{id}/approve`, which will update stock immediately (backward compatible).

---

### Step 2: Receive Goods (Create Bon de Livraison)

**Endpoint:** `POST /api/purchase-deliveries`

```json
{
    "order_purchase_id": 1,
    "delivery_date": "2026-06-10",
    "received_by": 2,
    "supplier_delivery_note": "SUPP-BL-12345",
    "transport_company": "Express Transport",
    "driver_name": "John Doe",
    "vehicle_plate": "ABC-123",
    "delivery_note": "Good condition",
    "quality_check_note": "All items inspected",
    "has_issues": false,
    "items": [
        {
            "order_purchase_item_id": 1,
            "delivered_quantity": 50,
            "accepted_quantity": 50,
            "rejected_quantity": 0,
            "batch_number": "BATCH-001",
            "expiry_date": "2026-12-31"
        }
    ]
}
```

**What happens:**

- ✅ BL created with number (BL-0001)
- ✅ Status = "draft"
- ❌ Stock NOT updated yet (only after validation)
- ✅ Purchase order status → "PARTIALLY_RECEIVED"
- ✅ `received_quantity` updated in order items
- ✅ `remaining_quantity` calculated

---

### Step 3: Validate Delivery (Update Stock)

**Endpoint:** `POST /api/purchase-deliveries/{id}/validate`

**What happens:**

- ✅ Stock updated with `accepted_quantity`
- ✅ Stock movement created (type: purchase, direction: in)
- ✅ Delivery status → "validated"
- ✅ Purchase order checked for completeness:
    - If all items received → status = "FULLY_RECEIVED"
    - If some items received → status = "PARTIALLY_RECEIVED"

---

### Step 4: View All Deliveries for a Purchase Order

**Endpoint:** `GET /api/purchases/{purchaseOrderId}/deliveries`

**Response:**

```json
{
    "deliveries": [
        {
            "id": 1,
            "delivery_number": "BL-0001",
            "delivery_date": "2026-06-10",
            "status": "validated",
            "status_label": "Validé",
            "delivery_items": [
                {
                    "product_id": 1,
                    "ordered_quantity": 100,
                    "delivered_quantity": 50,
                    "accepted_quantity": 50,
                    "rejected_quantity": 0
                }
            ]
        }
    ]
}
```

---

## 📋 Database Migration Steps

When your database is available, run:

```bash
# 1. Run migrations
php artisan migrate

# 2. Seed delivery settings
php artisan db:seed --class=DeliverySettingsSeeder
```

This will:

- Create `purchase_deliveries` table
- Create `purchase_delivery_items` table
- Add new columns to `order_purchases`
- Add new columns to `order_purchase_items`
- Create settings for delivery number sequence

---

## ⚠️ Important Notes

### Backward Compatibility ✅

The old workflow still works:

- `POST /api/purchases` - Create purchase order
- `PUT /api/purchases/{id}/approve` - Approve and update stock immediately
- All existing functionality preserved

### New Workflow (Optional)

- Use the new delivery endpoints for more control
- Track partial deliveries
- Quality checks and rejections
- Multiple deliveries per purchase order

### When to Use Which Workflow?

**Use Old Workflow (Direct Approval):**

- Simple purchases
- Immediate stock updates
- No quality checks needed
- Single delivery expected

**Use New Workflow (Delivery Notes):**

- Complex purchases
- Multiple deliveries
- Quality control required
- Supplier performance tracking
- Partial deliveries expected
- Need audit trail

---

## 🔄 Workflow Comparison

### OLD: Direct Approval

```
1. Create Purchase (Brouillon)
   ↓
2. Approve Purchase
   ↓
3. Stock Updated Immediately
   ↓
4. Status = COMPLETED
```

### NEW: With Delivery Notes

```
1. Create Purchase (Brouillon)
   ↓
2. (Optional) Approve → Status = ORDERED
   ↓
3. Create Delivery Note (BL-0001)
   ↓
4. Validate Delivery
   ↓
5. Stock Updated for Accepted Quantity
   ↓
6. Status = PARTIALLY_RECEIVED or FULLY_RECEIVED
   ↓
7. (Optional) Create More Deliveries
```

---

## 📊 Data Tracking

### Purchase Order Item Tracking

```
Product: Chicken
- Ordered: 100 kg
- Received: 50 kg (from BL-0001)
- Remaining: 50 kg
- Status: Partially Received
```

### Delivery Item Tracking

```
BL-0001:
- Delivered: 50 kg
- Accepted: 48 kg
- Rejected: 2 kg
- Reason: "Damaged packaging"
```

### Stock Movement

```
Type: Purchase
Direction: In
Quantity: 48 kg (accepted only)
Reference: BL-0001
```

---

## 🎯 Next Steps (Optional Enhancements)

### 1. PDF Generation

Create service to generate PDF for:

- Bon de Commande (Purchase Order)
- Bon de Livraison (Delivery Note)

### 2. Email Notifications

Notify when:

- Purchase order created
- Delivery received
- Quality issues detected

### 3. Supplier Portal

Allow suppliers to:

- View purchase orders
- Update delivery status
- Upload delivery documents

### 4. Analytics Dashboard

Track:

- Supplier delivery performance
- Quality rejection rates
- Average delivery time
- Partial delivery frequency

---

## 📞 Support & Questions

If you need help:

1. Check this guide
2. Review the API endpoints
3. Test with Postman/Thunder Client
4. Check logs in `storage/logs/`

---

_Implementation completed: June 3, 2026_
_All existing functionality preserved_
_New delivery workflow is optional_
