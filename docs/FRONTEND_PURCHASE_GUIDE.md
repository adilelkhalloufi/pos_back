# Frontend Purchase System Guide

## 📋 TypeScript Interfaces

```typescript
// Purchase Order
interface PurchaseOrder {
  id: number;
  order_number: string;
  reference: string;
  status: number;
  status_label: string;
  delivery_status: 'not_started' | 'partially_received' | 'fully_received';
  supplier: {
    id: number;
    name: string;
  };
  ordered_date: string;
  expected_delivery_date: string;
  total_amount: number;
  total_ordered: number;
  total_received: number;
  total_remaining: number;
  completion_percentage: number;
  items: PurchaseOrderItem[];
  deliveries?: PurchaseDelivery[];
}

interface PurchaseOrderItem {
  id: number;
  product_id: number;
  product: {
    id: number;
    name: string;
    codebar: string;
  };
  quantity: number;
  received_quantity: number;
  remaining_quantity: number;
  price: number;
  total: number;
}

// Delivery
interface PurchaseDelivery {
  id: number;
  delivery_number: string;
  order_purchase_id: number;
  delivery_date: string;
  status: 'draft' | 'validated' | 'cancelled';
  status_label: string;
  total_delivered: number;
  total_accepted: number;
  total_rejected: number;
  delivery_items: DeliveryItem[];
}

interface DeliveryItem {
  id: number;
  product_id: number;
  product: { id: number; name: string };
  ordered_quantity: number;
  delivered_quantity: number;
  accepted_quantity: number;
  rejected_quantity: number;
  unit_price: number;
  total_price: number;
}
```

---

## 🔗 API Endpoints to Use

```typescript
// Base API URL
const API_URL = 'http://your-domain.com/api';

// Purchase Orders
GET    /purchases                       // List all orders
GET    /purchases/{id}                  // Get order details
POST   /purchases                       // Create order
PUT    /purchases/{id}                  // Update order
DELETE /purchases/{id}                  // Delete order
PUT    /purchases/{id}/approve          // Approve order
PUT    /purchases/{id}/cancel           // Cancel order

// Deliveries
GET    /purchases/{id}/deliveries       // List deliveries for order
POST   /purchase-deliveries             // Create delivery
POST   /purchase-deliveries/{id}/validate  // Validate & update stock

// Related
GET    /suppliers                       // List suppliers
GET    /paid_methods                    // Payment methods
GET    /store-products                  // Products list
```

---

## 🎨 Pages to Create

### 1. **Purchase List Page** (`/purchases`)

**What to show:**
- Table with columns: Order Number, Supplier, Date, Status, Total, Progress
- Date filter (start/end date)
- Status badge with color
- Progress bar showing completion percentage
- Actions: View, Edit, Delete

**API Call:**
```typescript
const fetchPurchases = async (dateStart?: string, dateEnd?: string) => {
  const params = new URLSearchParams();
  if (dateStart) params.append('date_start', dateStart);
  if (dateEnd) params.append('date_end', dateEnd);
  
  const response = await http.get(`/purchases?${params}`);
  return response.data;
};
```

---

### 2. **Purchase Form** (`/purchases/create` or `/purchases/:id/edit`)

**What to show:**
- Supplier dropdown
- Expected delivery date picker
- Payment method dropdown
- Reference input
- Notes textarea
- Product table with: Product, Quantity, Price, Total
- Add/Remove product rows
- Total amount display

**API Calls:**
```typescript
// Create
const createPurchase = async (data) => {
  const response = await http.post('/purchases', {
    supplier_id: data.supplier_id,
    expected_delivery_date: data.expected_delivery_date,
    reference: data.reference,
    details: data.products.map(p => ({
      product_id: p.product_id,
      quantity: p.quantity,
      price: p.price
    }))
  });
  return response.data;
};

// Update
const updatePurchase = async (id, data) => {
  return await http.put(`/purchases/${id}`, data);
};
```

---

### 3. **Purchase Detail Page** (`/purchases/:id`)

**What to show:**
- Order info: Number, Supplier, Status, Dates
- Progress card: Ordered / Received / Remaining
- Items table: Product, Ordered, Received, Remaining
- Deliveries list with status badges
- Actions: Approve, Create Delivery, Cancel

**API Call:**
```typescript
const fetchPurchaseDetail = async (id: number) => {
  const response = await http.get(`/purchases/${id}`);
  return response.data;
};

const approvePurchase = async (id: number) => {
  return await http.put(`/purchases/${id}/approve`);
};
```

---

### 4. **Create Delivery Dialog** (Modal/Dialog)

**What to show:**
- Order items with remaining quantities
- For each item:
  - Product name
  - Ordered quantity (read-only)
  - Remaining quantity (read-only)
  - Delivered quantity input (max: remaining)
  - Accepted quantity input
  - Rejected quantity input
  - Rejection reason textarea (if rejected > 0)
- Delivery date picker
- Supplier BL number input
- Quality check notes

**API Call:**
```typescript
const createDelivery = async (data) => {
  const response = await http.post('/purchase-deliveries', {
    order_purchase_id: data.order_id,
    delivery_date: data.delivery_date,
    items: data.items.map(item => ({
      order_purchase_item_id: item.order_purchase_item_id,
      delivered_quantity: item.delivered_quantity,
      accepted_quantity: item.accepted_quantity,
      rejected_quantity: item.rejected_quantity,
      rejection_reason: item.rejection_reason
    }))
  });
  return response.data;
};

const validateDelivery = async (id: number) => {
  return await http.post(`/purchase-deliveries/${id}/validate`);
};
```

---

## 🎯 Key Frontend Logic

### Status Badge Colors
```typescript
const getStatusColor = (status: number) => {
  switch (status) {
    case 1: return 'gray';     // Draft
    case 15: return 'blue';    // Ordered
    case 17: return 'purple';  // Partially Received
    case 18: return 'green';   // Fully Received
    case 9: return 'red';      // Cancelled
    default: return 'gray';
  }
};
```

### Progress Bar
```typescript
const ProgressBar = ({ percentage }: { percentage: number }) => (
  <div className="w-full bg-gray-200 rounded">
    <div 
      className="bg-blue-600 h-2 rounded"
      style={{ width: `${percentage}%` }}
    />
    <span className="text-xs">{percentage}%</span>
  </div>
);
```

### Validation: Delivered = Accepted + Rejected
```typescript
const validateDeliveryItem = (item) => {
  const total = item.accepted_quantity + item.rejected_quantity;
  if (total !== item.delivered_quantity) {
    return 'Accepted + Rejected must equal Delivered';
  }
  if (item.delivered_quantity > item.remaining_quantity) {
    return 'Cannot deliver more than remaining';
  }
  return null;
};
```

---

## 📝 Complete Example Flow

```typescript
// 1. Create Purchase Order
const newOrder = await createPurchase({
  supplier_id: 1,
  expected_delivery_date: '2026-06-15',
  products: [
    { product_id: 10, quantity: 100, price: 10.50 }
  ]
});

// 2. Approve Order
await approvePurchase(newOrder.id);
// Note: Stock NOT updated yet

// 3. Create Delivery (partial)
const delivery = await createDelivery({
  order_id: newOrder.id,
  delivery_date: '2026-06-10',
  items: [{
    order_purchase_item_id: 1,
    delivered_quantity: 50,
    accepted_quantity: 48,
    rejected_quantity: 2,
    rejection_reason: 'Damaged'
  }]
});

// 4. Validate Delivery
await validateDelivery(delivery.id);
// Now stock is updated: +48 units

// 5. Check order progress
const updated = await fetchPurchaseDetail(newOrder.id);
console.log(updated.completion_percentage); // 48%
console.log(updated.delivery_status); // "partially_received"
```

---

## ✅ Summary

**Pages:** List, Form, Detail, Delivery Dialog  
**Key Feature:** Stock updates only when delivery is validated  
**Main APIs:** 8 endpoints total (3 for orders, 3 for deliveries, 2 related)  
**Critical Logic:** Validate quantities before submission  

That's it! Keep it simple. 🚀
