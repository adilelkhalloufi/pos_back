# Restaurant Menu Items Sale API

## Overview

This API endpoint allows the frontend to create restaurant sales orders directly with full menu item details.

## Endpoint

**POST** `/api/sell-menu-items`

### Authentication

Requires authentication token in the header.

### Request Headers

```
Authorization: Bearer {your_token}
Content-Type: application/json
```

## Request Body

```json
{
    "items": [
        {
            "id": 1,
            "name": "Test Grilled Chicken Salad",
            "price": 12.99,
            "cost": 2.7748,
            "image": null,
            "description": "Test menu item: Fresh salad with grilled chicken breast",
            "category_id": 1,
            "stock": 999,
            "qte": 3,
            "item_type": "recipe",
            "preparation_time_minutes": 25
        }
    ],
    "total_command": 38.97,
    "total_payment": 0,
    "discount": 0,
    "advance": 0,
    "customer_id": null,
    "payment_method_id": null,
    "note": "Optional order note"
}
```

### Required Fields

| Field           | Type    | Description                                   |
| --------------- | ------- | --------------------------------------------- |
| `items`         | array   | Array of menu items to sell (minimum 1 item)  |
| `items[].id`    | integer | Menu item ID (must exist in menu_items table) |
| `items[].name`  | string  | Menu item name                                |
| `items[].price` | number  | Menu item price                               |
| `items[].qte`   | number  | Quantity (must be > 0)                        |
| `total_command` | number  | Total order amount                            |

### Optional Fields

| Field                              | Type    | Description                          |
| ---------------------------------- | ------- | ------------------------------------ |
| `items[].cost`                     | number  | Item cost                            |
| `items[].image`                    | string  | Item image URL                       |
| `items[].description`              | string  | Item description                     |
| `items[].category_id`              | integer | Category ID                          |
| `items[].stock`                    | number  | Stock quantity                       |
| `items[].item_type`                | string  | Type: recipe, combo, simple, product |
| `items[].preparation_time_minutes` | integer | Preparation time                     |
| `total_payment`                    | number  | Initial payment amount (default: 0)  |
| `discount`                         | number  | Discount amount (default: 0)         |
| `advance`                          | number  | Advance payment (default: 0)         |
| `customer_id`                      | integer | Customer ID (if applicable)          |
| `payment_method_id`                | integer | Payment method ID                    |
| `note`                             | string  | Order notes                          |

## Response

### Success Response (201 Created)

```json
{
    "success": true,
    "order": {
        "id": 123,
        "order_number": "00001",
        "total_command": 38.97,
        "total_payment": 0,
        "rest_a_pay": 38.97,
        "status": "unpaid",
        "is_invoice": true,
        "customer_id": null,
        "user_id": 1,
        "store_id": 1,
        "created_at": "2026-05-24T10:30:00.000000Z",
        "order_items": [
            {
                "id": 456,
                "order_id": 123,
                "name": "Test Grilled Chicken Salad",
                "price": 12.99,
                "qte": 3,
                "total": 38.97
            }
        ],
        "payments": []
    },
    "message": "Menu items sold successfully"
}
```

### Error Response (400 Bad Request)

```json
{
    "success": false,
    "message": "Error selling menu items: Menu item 'Grilled Chicken Salad' is not available"
}
```

## Validation Rules

1. **Price Validation**: The system validates that the price sent from the frontend matches the database price (within 0.01 tolerance) to prevent price manipulation.

2. **Total Validation**: The system recalculates the total and validates against the provided `total_command` (within 0.05 tolerance).

3. **Availability Check**: Menu items must be active (`is_active = true`) and available (`is_available = true`).

4. **Stock Deduction**: Stock management for recipe-based and product-based items is pending implementation (see TODO in code).

## Order Status

The order status is automatically determined based on payment:

- **UNPAID**: `rest_a_pay > 0` and `advance = 0`
- **AVANCE** (Partial): `rest_a_pay > 0` and `advance > 0`
- **PAID**: `rest_a_pay = 0`

## Usage Examples

### JavaScript/Fetch Example

```javascript
async function sellMenuItems(items) {
    const response = await fetch("/api/sell-menu-items", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: `Bearer ${yourAuthToken}`,
        },
        body: JSON.stringify({
            items: items,
            total_command: items.reduce(
                (sum, item) => sum + item.price * item.qte,
                0,
            ),
            total_payment: 0,
        }),
    });

    return await response.json();
}
```

### Axios Example

```javascript
import axios from "axios";

const sellMenuItems = async (items) => {
    try {
        const response = await axios.post("/api/sell-menu-items", {
            items: items,
            total_command: items.reduce(
                (sum, item) => sum + item.price * item.qte,
                0,
            ),
            total_payment: 0,
        });

        return response.data;
    } catch (error) {
        console.error("Error selling menu items:", error.response.data);
        throw error;
    }
};
```

## Differences from `/api/restaurant-orders`

| Feature              | `/api/sell-menu-items`              | `/api/restaurant-orders`       |
| -------------------- | ----------------------------------- | ------------------------------ |
| **Data format**      | Full item details in request        | Only menu_item_id and quantity |
| **Frontend data**    | Uses complete item object           | Minimal data required          |
| **Price validation** | Validates frontend price against DB | Fetches price from DB          |
| **Use case**         | POS/frontend with full item data    | Backend/minimal frontend       |

## Notes

1. The endpoint creates an invoice by default (`is_invoice = true`).
2. Order numbers are auto-generated based on daily order count.
3. Payment records are created automatically if an advance payment is provided.
4. The order is loaded with related data (orderItems, user, customer, payments) in the response.

## Future Enhancements

- [ ] Implement stock deduction for recipe-based items
- [ ] Implement stock deduction for product-based items
- [ ] Add support for combo menu items
- [ ] Add support for modifiers/customizations
- [ ] Add table/seat assignment for dine-in orders
