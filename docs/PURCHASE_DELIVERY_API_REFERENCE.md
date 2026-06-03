# Purchase Delivery API - Quick Reference

## 📌 Overview

This API allows you to manage purchase deliveries (Bon de Livraison) separately from purchase orders (Bon de Commande). You can track partial deliveries, quality checks, and rejected items.

---

## 🔑 Endpoints

### 1. List Deliveries for a Purchase Order

```http
GET /api/purchases/{purchaseOrderId}/deliveries
```

**Response:**

```json
{
    "deliveries": [
        {
            "id": 1,
            "delivery_number": "BL-0001",
            "order_purchase_id": 5,
            "delivery_date": "2026-06-10",
            "status": "validated",
            "status_label": "Validé",
            "status_color": "green",
            "created_at": "2026-06-10 14:30",
            "delivery_items": [
                {
                    "product_id": 1,
                    "product": {
                        "id": 1,
                        "name": "Chicken",
                        "codebar": "123456"
                    },
                    "ordered_quantity": 100,
                    "delivered_quantity": 50,
                    "accepted_quantity": 50,
                    "rejected_quantity": 0,
                    "unit_price": 10.5,
                    "total_price": 525.0
                }
            ],
            "received_by": {
                "id": 2,
                "name": "John Doe"
            }
        }
    ]
}
```

---

### 2. Create New Delivery

```http
POST /api/purchase-deliveries
Content-Type: application/json
```

**Request Body:**

```json
{
    "order_purchase_id": 5,
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
            "order_purchase_item_id": 10,
            "delivered_quantity": 50,
            "accepted_quantity": 48,
            "rejected_quantity": 2,
            "rejection_reason": "Damaged packaging",
            "batch_number": "BATCH-001",
            "expiry_date": "2026-12-31"
        }
    ]
}
```

**Validation Rules:**

- `order_purchase_id` - required, must exist
- `delivery_date` - required, valid date
- `received_by` - optional, must exist in users
- `items` - required array, min 1 item
- `items.*.order_purchase_item_id` - required, must exist
- `items.*.delivered_quantity` - required, integer, min 1
- `items.*.accepted_quantity` - optional, integer, min 0
- `items.*.rejected_quantity` - optional, integer, min 0

**Response:**

```json
{
    "delivery": {
        "id": 1,
        "delivery_number": "BL-0001",
        "status": "draft",
        "delivery_items": [...]
    },
    "message": "Delivery created successfully"
}
```

---

### 3. View Delivery Details

```http
GET /api/purchase-deliveries/{id}
```

**Response:**

```json
{
    "delivery": {
        "id": 1,
        "delivery_number": "BL-0001",
        "order_purchase_id": 5,
        "delivery_date": "2026-06-10",
        "supplier_delivery_note": "SUPP-BL-12345",
        "transport_company": "Express Transport",
        "driver_name": "John Doe",
        "vehicle_plate": "ABC-123",
        "delivery_note": "Good condition",
        "quality_check_note": "All items inspected",
        "has_issues": false,
        "status": "draft",
        "status_label": "Brouillon",
        "status_color": "gray",
        "created_at": "2026-06-10 14:30",
        "order_purchase": {
            "id": 5,
            "order_number": "PUR-0005",
            "reference": "BC-2026-001"
        },
        "delivery_items": [
            {
                "id": 1,
                "product_id": 1,
                "product": {
                    "id": 1,
                    "name": "Chicken",
                    "codebar": "123456"
                },
                "ordered_quantity": 100,
                "delivered_quantity": 50,
                "accepted_quantity": 48,
                "rejected_quantity": 2,
                "rejection_reason": "Damaged packaging",
                "unit_price": 10.5,
                "total_price": 504.0,
                "batch_number": "BATCH-001",
                "expiry_date": "2026-12-31"
            }
        ],
        "received_by": {
            "id": 2,
            "name": "John Doe"
        }
    }
}
```

---

### 4. Validate Delivery (Update Stock)

```http
POST /api/purchase-deliveries/{id}/validate
```

**What it does:**

- Updates stock with accepted quantities
- Creates stock movements
- Updates purchase order item received quantities
- Changes delivery status to "validated"
- Checks if purchase order is fully received

**Response:**

```json
{
    "delivery": {
        "id": 1,
        "status": "validated",
        "status_label": "Validé"
    },
    "message": "Delivery validated successfully. Stock has been updated."
}
```

**Error Responses:**

- 404: Delivery not found
- 400: Only draft deliveries can be validated

---

### 5. Cancel Delivery

```http
POST /api/purchase-deliveries/{id}/cancel
```

**What it does:**

- Changes delivery status to "cancelled"
- Cannot cancel validated deliveries

**Response:**

```json
{
    "delivery": {
        "id": 1,
        "status": "cancelled",
        "status_label": "Annulé"
    },
    "message": "Delivery cancelled successfully"
}
```

**Error Responses:**

- 404: Delivery not found
- 400: Cannot cancel a validated delivery

---

## 📊 Delivery Status Flow

```
draft → validated
   ↓
cancelled (only from draft)
```

---

## 🔄 Complete Workflow Example

### Scenario: Order 100kg chicken, receive in 2 deliveries

#### 1. Create Purchase Order

```bash
POST /api/purchases
{
    "supplier_id": 1,
    "reference": "BC-2026-001",
    "details": [
        {
            "product_id": 1,
            "name": "Chicken",
            "quantity": 100,
            "price": 10.50,
            "total": 1050,
            "store_id": 1
        }
    ]
}
# Response: Purchase created with ID 5, status PENDING
```

#### 2. First Delivery (50kg)

```bash
POST /api/purchase-deliveries
{
    "order_purchase_id": 5,
    "delivery_date": "2026-06-10",
    "items": [
        {
            "order_purchase_item_id": 10,
            "delivered_quantity": 50,
            "accepted_quantity": 50,
            "rejected_quantity": 0
        }
    ]
}
# Response: BL-0001 created, status draft
```

#### 3. Validate First Delivery

```bash
POST /api/purchase-deliveries/1/validate
# Result:
# - Stock +50kg
# - Purchase item: received=50, remaining=50
# - Purchase order status: PARTIALLY_RECEIVED
```

#### 4. Second Delivery (50kg)

```bash
POST /api/purchase-deliveries
{
    "order_purchase_id": 5,
    "items": [
        {
            "order_purchase_item_id": 10,
            "delivered_quantity": 50,
            "accepted_quantity": 48,
            "rejected_quantity": 2,
            "rejection_reason": "Damaged"
        }
    ]
}
# Response: BL-0002 created
```

#### 5. Validate Second Delivery

```bash
POST /api/purchase-deliveries/2/validate
# Result:
# - Stock +48kg
# - Purchase item: received=98, remaining=2
# - Purchase order status: PARTIALLY_RECEIVED (2kg still missing)
```

---

## 💡 Tips

### Quality Control

- Use `accepted_quantity` to record items that passed quality checks
- Use `rejected_quantity` + `rejection_reason` to track issues
- Set `has_issues: true` if there are problems

### Partial Deliveries

- You can create multiple deliveries for one purchase order
- Each delivery can contain different items
- System automatically tracks received vs remaining quantities

### Batch Tracking

- Use `batch_number` to track product batches
- Use `expiry_date` for perishable items

### Stock Updates

- Stock is ONLY updated when you validate a delivery
- Draft deliveries don't affect stock
- Only `accepted_quantity` is added to stock

---

## ⚠️ Common Errors

### "Delivered quantity exceeds remaining quantity"

- You're trying to receive more than ordered
- Check `remaining_quantity` in purchase order items

### "Only draft deliveries can be validated"

- Delivery already validated
- Cannot re-validate

### "Cannot cancel a validated delivery"

- Stock already updated
- Create an adjustment instead

---

## 🔍 Testing with Postman/Thunder Client

1. Get purchase order ID: `GET /api/purchases`
2. Create delivery: `POST /api/purchase-deliveries`
3. View delivery: `GET /api/purchase-deliveries/{id}`
4. Validate: `POST /api/purchase-deliveries/{id}/validate`
5. Check stock updated: `GET /api/store-products`

---

_API Version: 1.0_
_Last Updated: June 3, 2026_
