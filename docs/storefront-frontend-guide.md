# Storefront Frontend Integration Guide

This guide explains how the frontend should:

1. Render a public landing page for each store.
2. Place online orders from that landing page.
3. Verify stock deduction after each order.

---

## 1) Overview

The storefront is slug-based:

- Each store has a `public_slug` (example: `my-fashion-shop`).
- Public customers use this slug to browse products and place orders.
- Store owner/admin uses authenticated APIs to publish products and see incoming orders.

---

## 2) API Endpoints

## Public (no auth)

### Get landing page data

`GET /api/public/stores/{slug}`

Returns:
- Store public info (title, banner, description, phone, address)
- Published products
- Product variants (color/size/material/gemstone/custom attributes)

### Place order

`POST /api/public/stores/{slug}/orders`

Creates a storefront order and deducts stock.

---

## Private (auth)

### Storefront settings

- `GET /api/storefront/settings`
- `PUT /api/storefront/settings`

### Publish/unpublish storefront products

- `GET /api/storefront/products`
- `POST /api/storefront/products`
- `PUT /api/storefront/products/{id}`
- `DELETE /api/storefront/products/{id}`

### Storefront orders

- `GET /api/storefront/orders`
- `GET /api/storefront/orders/{id}`

### Product variants (clothing/jewelry)

- `GET /api/products/{product}/variants`
- `POST /api/products/{product}/variants`
- `PUT /api/products/{product}/variants/{variant}`
- `DELETE /api/products/{product}/variants/{variant}`

---

## 3) Setup Flow (Admin side)

## Step A: Enable storefront + slug

Request:

```http
PUT /api/storefront/settings
Content-Type: application/json
Authorization: Bearer <token>
```

```json
{
  "public_slug": "my-fashion-shop",
  "storefront_enabled": true,
  "storefront_title": "My Fashion Shop",
  "storefront_description": "Clothing and jewelry delivered fast",
  "storefront_banner": "https://cdn.example.com/banner.jpg"
}
```

## Step B: (Optional) Add product variants

Example for a T-shirt:

```http
POST /api/products/10/variants
```

```json
{
  "sku": "TS-BLK-M",
  "color": "Black",
  "size": "M",
  "material": "Cotton",
  "price": 179.0,
  "stock": 14,
  "is_active": true,
  "attributes": {
    "fit": "regular"
  }
}
```

Example for jewelry:

```json
{
  "sku": "RING-GOLD-52",
  "color": "Gold",
  "size": "52",
  "material": "18K Gold",
  "gemstone": "Diamond",
  "weight_grams": 5.2,
  "price": 3200,
  "stock": 3,
  "is_active": true
}
```

## Step C: Publish product to storefront

```http
POST /api/storefront/products
```

```json
{
  "product_id": 10,
  "is_published": true,
  "sort_order": 1,
  "short_description": "Best-selling item",
  "cover_image": "https://cdn.example.com/p10.jpg"
}
```

---

## 4) Frontend Landing Page Flow (Public)

## Step 1: Resolve store slug from URL

Example URL:

`https://your-frontend.com/store/my-fashion-shop`

Slug = `my-fashion-shop`

## Step 2: Load storefront payload

```http
GET /api/public/stores/my-fashion-shop
```

Render:
- Hero/banner/title from `store`.
- Product grid from `products`.
- Variant selector from `products[].variants` (color, size, material, etc.).

## Step 3: Build cart line format

Each cart line should include:
- `product_id` (required)
- `quantity` (required)
- `variant_id` (optional, but required if user selected a variant)

---

## 5) Place Order Flow (Public)

Request:

```http
POST /api/public/stores/my-fashion-shop/orders
Content-Type: application/json
```

```json
{
  "customer_name": "Sara Ben",
  "customer_phone": "+212600000000",
  "customer_email": "sara@example.com",
  "customer_address": "Casablanca",
  "note": "Call before delivery",
  "currency": "MAD",
  "items": [
    {
      "product_id": 10,
      "variant_id": 22,
      "quantity": 2
    },
    {
      "product_id": 15,
      "quantity": 1
    }
  ]
}
```

Success response: `201`

```json
{
  "message": "Order placed successfully.",
  "order": {
    "id": 123,
    "order_number": "SF-20261020-00001",
    "status": "pending"
  }
}
```

---

## 6) Stock Deduction Rules (Verified)

Stock deduction is implemented in [StorefrontOrderService.php](../app/Services/Storefront/StorefrontOrderService.php):

- Order creation is wrapped in **DB transaction**.
- For each order item:
  - Product/store row is locked with `lockForUpdate()`.
  - If `variant_id` is sent:
    - Variant is validated and locked.
    - `product_variants.stock` is decremented.
  - If product is stockable:
    - `store_products.stock` is decremented.
- If stock is not enough, API returns `422` and transaction is rolled back.

This ensures order + stock updates are atomic and consistent.

---

## 7) How to Verify Deduction (QA checklist)

Use this repeatable scenario:

1. **Before order**:
   - Get product stock from admin API: `GET /api/store-products`
   - Get variant stock (if variant item): `GET /api/products/{product}/variants`
2. Place public order: `POST /api/public/stores/{slug}/orders`
3. **After order**:
   - Call same admin endpoints again
   - Check:
     - `store_products.stock` decreased by ordered quantity
     - and if variant used, `product_variants.stock` decreased too

Expected:
- Non-variant order: only base stock decreases.
- Variant order: base stock decreases (if stockable) + variant stock decreases.

---

## 8) Frontend Error Handling

When order API returns `422`, show backend message directly to user, for example:
- `Product is not published in storefront.`
- `Variant not found or inactive.`
- `Not enough variant stock.`
- `Not enough product stock.`

Recommended UX:
- Disable checkout while submitting.
- On success: show order number and clear cart.
- On 422: keep cart and show actionable error.

---

## 9) Demo Seeder (quick test data)

You can load demo data for storefront with:

```bash
php artisan db:seed --class=Database\\Seeders\\StorefrontDemoSeeder
```

What it seeds:
- Storefront settings (`public_slug`, enabled, title/description)
- 2 products (t-shirt + ring)
- Store product stock rows
- Clothing and jewelry variants (color/size/material/gemstone)
- Published storefront products
- 1 demo order created through the storefront service (so stock deduction is applied)
