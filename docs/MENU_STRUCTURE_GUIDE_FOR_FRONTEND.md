# Menu Structure Guide for Frontend Developers

**Last Updated:** May 23, 2026  
**Audience:** Frontend Developers  
**Purpose:** Complete guide to understanding and implementing the restaurant menu system

---

## 📋 Table of Contents

1. [Menu Hierarchy Overview](#menu-hierarchy-overview)
2. [Step-by-Step Creation Flow](#step-by-step-creation-flow)
3. [Complete API Reference](#complete-api-reference)
4. [Frontend UI Examples](#frontend-ui-examples)
5. [Data Models & Relationships](#data-models--relationships)
6. [Common Use Cases](#common-use-cases)
7. [Validation & Error Handling](#validation--error-handling)

---

## Menu Hierarchy Overview

### 3-Level Structure

```
┌─────────────────────────────────────────────┐
│  MENU (Top Level)                           │
│  Example: "Lunch Menu" (11am - 3pm)         │
│                                             │
│  ┌───────────────────────────────────────┐ │
│  │  MENU CATEGORY (Second Level)         │ │
│  │  Example: "Main Courses"              │ │
│  │                                       │ │
│  │  ┌─────────────────────────────────┐ │ │
│  │  │  MENU ITEM (Sellable Item)      │ │ │
│  │  │  - Grilled Chicken Salad        │ │ │
│  │  │  - Beef Burger                  │ │ │
│  │  │  - Vegetarian Pizza             │ │ │
│  │  └─────────────────────────────────┘ │ │
│  │                                       │ │
│  │  ┌─────────────────────────────────┐ │ │
│  │  │  MENU ITEM                      │ │ │
│  │  │  - Coca Cola                    │ │ │
│  │  │  - Orange Juice                 │ │ │
│  │  └─────────────────────────────────┘ │ │
│  └───────────────────────────────────────┘ │
│                                             │
│  ┌───────────────────────────────────────┐ │
│  │  MENU CATEGORY                        │ │
│  │  Example: "Beverages"                 │ │
│  │                                       │ │
│  │  [Menu Items...]                      │ │
│  └───────────────────────────────────────┘ │
└─────────────────────────────────────────────┘
```

### Real-World Example

```
Lunch Menu (11am - 3pm)
├── Main Courses
│   ├── Grilled Chicken Salad ($12.99)
│   ├── Beef Burger ($10.99)
│   └── Vegetarian Pizza ($14.99)
├── Beverages
│   ├── Coca Cola ($3.00)
│   ├── Orange Juice ($4.50)
│   └── Coffee ($2.50)
└── Desserts
    ├── Chocolate Cake ($6.50)
    └── Ice Cream ($5.00)

Dinner Menu (5pm - 10pm)
├── Appetizers
│   └── ...
└── Main Courses
    └── ...
```

---

## Step-by-Step Creation Flow

### Flow Chart

```
START
  ↓
1. Create MENU (Lunch Menu, Dinner Menu, etc.)
  ↓
2. Create MENU CATEGORIES under the Menu
   (Main Courses, Beverages, Desserts, etc.)
  ↓
3. Create MENU ITEMS under each Category
   (Grilled Chicken, Coca Cola, etc.)
  ↓
4. Menu is ready for POS/ordering
  ↓
END
```

---

## Complete API Reference

### STEP 1: Create Menu

**Endpoint:** `POST /api/menus`

**Headers:**

```http
Content-Type: application/json
X-Store-ID: 1
Authorization: Bearer {token}
```

**Request Body:**

```json
{
    "name": "Lunch Menu",
    "description": "Available during lunch hours",
    "is_active": true,
    "available_from_time": "11:00:00",
    "available_to_time": "15:00:00",
    "available_days": ["monday", "tuesday", "wednesday", "thursday", "friday"],
    "display_order": 1
}
```

**Response:**

```json
{
    "id": 1,
    "name": "Lunch Menu",
    "description": "Available during lunch hours",
    "is_active": true,
    "available_from_time": "11:00:00",
    "available_to_time": "15:00:00",
    "available_days": ["monday", "tuesday", "wednesday", "thursday", "friday"],
    "display_order": 1,
    "store_id": 1,
    "created_at": "2026-05-23T10:00:00Z",
    "updated_at": "2026-05-23T10:00:00Z"
}
```

**Field Explanations:**

| Field                 | Type             | Required | Description                                    |
| --------------------- | ---------------- | -------- | ---------------------------------------------- |
| `name`                | string (max 100) | ✅ Yes   | Menu name displayed to customers               |
| `description`         | text             | ❌ No    | Additional details about the menu              |
| `is_active`           | boolean          | ❌ No    | Enable/disable entire menu (default: true)     |
| `available_from_time` | time             | ❌ No    | Start time (HH:MM:SS format, e.g., "11:00:00") |
| `available_to_time`   | time             | ❌ No    | End time (HH:MM:SS format, e.g., "15:00:00")   |
| `available_days`      | array            | ❌ No    | Days menu is available (monday-sunday)         |
| `display_order`       | integer          | ❌ No    | Sort order for displaying menus (default: 0)   |

---

### STEP 2: Create Menu Categories

**Endpoint:** `POST /api/menu-categories`

**Request Body:**

```json
{
    "menu_id": 1,
    "name": "Main Courses",
    "description": "Our signature main dishes",
    "is_active": true,
    "display_order": 1
}
```

**Response:**

```json
{
    "id": 1,
    "menu_id": 1,
    "name": "Main Courses",
    "description": "Our signature main dishes",
    "is_active": true,
    "display_order": 1,
    "created_at": "2026-05-23T10:05:00Z",
    "updated_at": "2026-05-23T10:05:00Z"
}
```

**Field Explanations:**

| Field           | Type             | Required | Description                             |
| --------------- | ---------------- | -------- | --------------------------------------- |
| `menu_id`       | integer          | ✅ Yes   | ID of the parent menu (from Step 1)     |
| `name`          | string (max 100) | ✅ Yes   | Category name (e.g., "Main Courses")    |
| `description`   | text             | ❌ No    | Category description                    |
| `is_active`     | boolean          | ❌ No    | Enable/disable category (default: true) |
| `display_order` | integer          | ❌ No    | Sort order within the menu (default: 0) |

---

### STEP 3: Create Menu Items

Menu items can be created in **4 different ways** depending on the type:

#### Option A: Recipe-Based Menu Item (Prepared Dishes)

**Best for:** Dishes made from multiple ingredients (salads, burgers, pasta)

**Endpoint:** `POST /api/menu-items`

**Request Body:**

```json
{
    "menu_category_id": 1,
    "name": "Grilled Chicken Salad",
    "description": "Fresh salad with grilled chicken breast, lettuce, and tomatoes",
    "image": "menu_items/chicken_salad.jpg",
    "price": 12.99,
    "item_type": "recipe",
    "recipe_id": 1,
    "is_active": true,
    "is_available": true,
    "preparation_time_minutes": 15,
    "display_order": 1
}
```

**Response:**

```json
{
    "id": 1,
    "menu_category_id": 1,
    "name": "Grilled Chicken Salad",
    "description": "Fresh salad with grilled chicken breast, lettuce, and tomatoes",
    "image": "http://yourapi.com/storage/menu_items/chicken_salad.jpg",
    "price": 12.99,
    "cost": 3.98,
    "item_type": "recipe",
    "recipe_id": 1,
    "product_id": null,
    "is_active": true,
    "is_available": true,
    "preparation_time_minutes": 15,
    "display_order": 1,
    "food_cost_percentage": 30.6,
    "profit_margin": 9.01,
    "recipe": {
        "id": 1,
        "name": "Grilled Chicken Salad Recipe",
        "total_cost": 3.98
    }
}
```

---

#### Option B: Product-Based Menu Item (Direct Product Sales)

**Best for:** Items sold as-is without preparation (drinks, packaged items)

**Endpoint:** `POST /api/menu-items`

**Request Body:**

```json
{
    "menu_category_id": 2,
    "name": "Coca Cola",
    "description": "330ml bottle",
    "image": "menu_items/coca_cola.jpg",
    "price": 3.0,
    "item_type": "product",
    "product_id": 15,
    "is_active": true,
    "is_available": true,
    "display_order": 1
}
```

**Response:**

```json
{
    "id": 2,
    "menu_category_id": 2,
    "name": "Coca Cola",
    "description": "330ml bottle",
    "image": "http://yourapi.com/storage/menu_items/coca_cola.jpg",
    "price": 3.0,
    "cost": 1.0,
    "item_type": "product",
    "recipe_id": null,
    "product_id": 15,
    "is_active": true,
    "is_available": true,
    "food_cost_percentage": 33.33,
    "profit_margin": 2.0,
    "product": {
        "id": 15,
        "name": "Coca Cola 330ml",
        "price_buy": 1.0
    }
}
```

---

#### Option C: Simple Menu Item (Manual Cost)

**Best for:** Services, fees, or items without inventory tracking

**Endpoint:** `POST /api/menu-items`

**Request Body:**

```json
{
    "menu_category_id": 3,
    "name": "Delivery Fee",
    "description": "Standard delivery charge",
    "price": 5.0,
    "cost": 2.0,
    "item_type": "simple",
    "is_active": true,
    "display_order": 999
}
```

**Response:**

```json
{
    "id": 3,
    "menu_category_id": 3,
    "name": "Delivery Fee",
    "description": "Standard delivery charge",
    "price": 5.0,
    "cost": 2.0,
    "item_type": "simple",
    "recipe_id": null,
    "product_id": null,
    "is_active": true,
    "profit_margin": 3.0
}
```

---

#### Option D: Combo Menu Item (Bundle Deal)

**Best for:** Meal deals, family meals, combo offers

**Endpoint:** `POST /api/menu-items`

**Request Body:**

```json
{
    "menu_category_id": 1,
    "name": "Family Meal Deal",
    "description": "2 Main courses + 4 Drinks",
    "price": 35.0,
    "item_type": "combo",
    "is_active": true,
    "combo_items": [
        {
            "menu_item_id": 1,
            "quantity": 2
        },
        {
            "menu_item_id": 2,
            "quantity": 4
        }
    ]
}
```

---

### Menu Item Field Reference

| Field                      | Type             | Required       | Description                                     |
| -------------------------- | ---------------- | -------------- | ----------------------------------------------- |
| `menu_category_id`         | integer          | ✅ Yes         | Parent category ID (from Step 2)                |
| `name`                     | string (max 100) | ✅ Yes         | Item name shown to customers                    |
| `description`              | text             | ❌ No          | Item description                                |
| `image`                    | string           | ❌ No          | Image path/URL                                  |
| `price`                    | decimal          | ✅ Yes         | Selling price                                   |
| `cost`                     | decimal          | ❌ No          | Cost (auto-calculated for recipe/product types) |
| `item_type`                | enum             | ✅ Yes         | `recipe`, `product`, `simple`, or `combo`       |
| `recipe_id`                | integer          | ❌ Conditional | Required if `item_type = recipe`                |
| `product_id`               | integer          | ❌ Conditional | Required if `item_type = product`               |
| `is_active`                | boolean          | ❌ No          | Enable/disable item (default: true)             |
| `is_available`             | boolean          | ❌ No          | Temporary availability (default: true)          |
| `preparation_time_minutes` | integer          | ❌ No          | Estimated prep time                             |
| `display_order`            | integer          | ❌ No          | Sort order in category                          |

---

## Frontend UI Examples

### 1. Menu Management Screen

```
┌─────────────────────────────────────────────────────────────┐
│  Menus Management                        [+ Create Menu]     │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  🍽️ Lunch Menu                  [Edit] [Delete] [⚙️]  │  │
│  │  Available: 11:00 AM - 3:00 PM  ✅ Active             │  │
│  │  Days: Mon - Fri                                      │  │
│  │                                                        │  │
│  │  Categories (3):                    [+ Add Category]   │  │
│  │                                                        │  │
│  │  ├─ 🍖 Main Courses (12 items)     [Edit] [Delete]   │  │
│  │  ├─ 🥤 Beverages (8 items)         [Edit] [Delete]   │  │
│  │  └─ 🍰 Desserts (5 items)          [Edit] [Delete]   │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  🍽️ Dinner Menu                 [Edit] [Delete] [⚙️]  │  │
│  │  Available: 5:00 PM - 10:00 PM  ✅ Active             │  │
│  │  Days: All Week                                       │  │
│  │                                                        │  │
│  │  Categories (4):                    [+ Add Category]   │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

### 2. Menu Category Detail Screen

```
┌─────────────────────────────────────────────────────────────┐
│  ← Back to Menus                                             │
│                                                               │
│  Main Courses                                   [Edit Info]  │
│  Under: Lunch Menu                                           │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  [+ Add Menu Item]                      [⚙️ Bulk Actions]    │
│                                                               │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  [📷]  Grilled Chicken Salad              $12.99       │  │
│  │        Recipe-based • Cost: $3.98 • Margin: 69%       │  │
│  │        ✅ Active ✅ Available • Prep: 15 min           │  │
│  │        [Edit] [Duplicate] [Toggle] [Delete]           │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  [📷]  Beef Burger                        $10.99       │  │
│  │        Recipe-based • Cost: $4.50 • Margin: 59%       │  │
│  │        ✅ Active ⚠️ Out of Stock                        │  │
│  │        [Edit] [Duplicate] [Toggle] [Delete]           │  │
│  └───────────────────────────────────────────────────────┘  │
│                                                               │
│  ┌───────────────────────────────────────────────────────┐  │
│  │  [📷]  Vegetarian Pizza                   $14.99       │  │
│  │        Recipe-based • Cost: $5.20 • Margin: 65%       │  │
│  │        ✅ Active ✅ Available • Prep: 20 min           │  │
│  │        [Edit] [Duplicate] [Toggle] [Delete]           │  │
│  └───────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────┘
```

---

### 3. Create Menu Item Modal

```
┌─────────────────────────────────────────────────────────────┐
│  Create Menu Item                                      [✕]   │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  Item Type:  ○ Recipe-Based  ⦿ Product-Based  ○ Simple      │
│                                                               │
│  Basic Information                                            │
│  ┌─────────────────────────────────────────────────────┐    │
│  │ Name *          [Coca Cola                        ] │    │
│  │                                                      │    │
│  │ Description     [330ml bottle                     ] │    │
│  │                                                      │    │
│  │ Category *      [Beverages ▼]                       │    │
│  │                                                      │    │
│  │ Price *         [$3.00                            ] │    │
│  │                                                      │    │
│  │ Product *       [Search products...] 🔍             │    │
│  │                 Selected: Coca Cola 330ml           │    │
│  │                 Purchase Cost: $1.00                │    │
│  │                                                      │    │
│  │ Image           [📁 Upload Image]                   │    │
│  │                                                      │    │
│  │ Prep Time       [0         ] minutes                │    │
│  │                                                      │    │
│  │ □ Active        ☑ Available                         │    │
│  └─────────────────────────────────────────────────────┘    │
│                                                               │
│  Calculated Metrics:                                          │
│  • Cost: $1.00 (auto from product)                          │
│  • Food Cost %: 33.33%                                       │
│  • Profit Margin: $2.00 (66.67%)                            │
│                                                               │
│                                [Cancel]  [Create Item]       │
└─────────────────────────────────────────────────────────────┘
```

---

## Data Models & Relationships

### Database Schema

```sql
menus
├── id (PK)
├── name
├── description
├── is_active
├── available_from_time
├── available_to_time
├── available_days
├── display_order
└── store_id (FK)

menu_categories
├── id (PK)
├── menu_id (FK) → menus.id
├── name
├── description
├── is_active
└── display_order

menu_items
├── id (PK)
├── menu_category_id (FK) → menu_categories.id
├── name
├── description
├── image
├── price
├── cost
├── item_type (recipe|product|simple|combo)
├── recipe_id (FK) → recipes.id (nullable)
├── product_id (FK) → products.id (nullable)
├── is_active
├── is_available
├── preparation_time_minutes
└── display_order
```

### JSON Response Structure

```json
{
    "menu": {
        "id": 1,
        "name": "Lunch Menu",
        "categories": [
            {
                "id": 1,
                "name": "Main Courses",
                "items": [
                    {
                        "id": 1,
                        "name": "Grilled Chicken Salad",
                        "price": 12.99,
                        "cost": 3.98,
                        "item_type": "recipe",
                        "is_available": true
                    },
                    {
                        "id": 2,
                        "name": "Coca Cola",
                        "price": 3.0,
                        "cost": 1.0,
                        "item_type": "product",
                        "is_available": true
                    }
                ]
            },
            {
                "id": 2,
                "name": "Beverages",
                "items": []
            }
        ]
    }
}
```

---

## Common Use Cases

### Use Case 1: Restaurant with Multiple Menus

**Scenario:** Restaurant serves different items at different times

**Implementation:**

```javascript
// Create breakfast menu
POST /api/menus
{
  "name": "Breakfast Menu",
  "available_from_time": "07:00:00",
  "available_to_time": "11:00:00"
}

// Create lunch menu
POST /api/menus
{
  "name": "Lunch Menu",
  "available_from_time": "11:00:00",
  "available_to_time": "15:00:00"
}

// Create dinner menu
POST /api/menus
{
  "name": "Dinner Menu",
  "available_from_time": "17:00:00",
  "available_to_time": "22:00:00"
}
```

---

### Use Case 2: Fast Food with Drinks & Add-ons

**Scenario:** Burger restaurant with main items and beverages

**Implementation:**

```javascript
// Step 1: Create main menu
POST /api/menus { "name": "All Day Menu" }

// Step 2: Create categories
POST /api/menu-categories { "menu_id": 1, "name": "Burgers" }
POST /api/menu-categories { "menu_id": 1, "name": "Beverages" }
POST /api/menu-categories { "menu_id": 1, "name": "Sides" }

// Step 3: Add recipe-based burgers
POST /api/menu-items {
  "menu_category_id": 1,
  "name": "Classic Burger",
  "price": 8.99,
  "item_type": "recipe",
  "recipe_id": 1
}

// Step 4: Add product-based drinks
POST /api/menu-items {
  "menu_category_id": 2,
  "name": "Coca Cola",
  "price": 2.50,
  "item_type": "product",
  "product_id": 15
}
```

---

### Use Case 3: Combo Meals

**Scenario:** Create value meals with multiple items

**Implementation:**

```javascript
// Create combo in "Combo Meals" category
POST /api/menu-items
{
  "menu_category_id": 4,
  "name": "Burger Combo",
  "description": "Burger + Fries + Drink",
  "price": 12.99,
  "item_type": "combo",
  "combo_items": [
    { "menu_item_id": 1, "quantity": 1 },  // Classic Burger
    { "menu_item_id": 10, "quantity": 1 }, // Fries
    { "menu_item_id": 15, "quantity": 1 }  // Drink
  ]
}
```

---

## Validation & Error Handling

### Common Errors

#### Error 1: Invalid Menu ID

```json
{
    "error": "Menu category not found",
    "message": "The selected menu_id is invalid.",
    "status": 404
}
```

**Solution:** Verify menu exists before creating category

---

#### Error 2: Missing Required Recipe/Product

```json
{
    "error": "Validation error",
    "message": "recipe_id is required when item_type is 'recipe'",
    "status": 422
}
```

**Solution:** Ensure correct item_type and corresponding ID:

- `item_type: 'recipe'` → requires `recipe_id`
- `item_type: 'product'` → requires `product_id`
- `item_type: 'simple'` → requires `cost` field
- `item_type: 'combo'` → requires `combo_items` array

---

#### Error 3: Time Validation

```json
{
    "error": "Validation error",
    "message": "available_to_time must be after available_from_time",
    "status": 422
}
```

**Solution:** Ensure `available_to_time > available_from_time`

---

### Frontend Validation Checklist

Before submitting:

**Menu Creation:**

- [ ] Name is not empty (max 100 chars)
- [ ] If time-based, `from_time < to_time`
- [ ] Store ID is selected

**Category Creation:**

- [ ] Name is not empty (max 100 chars)
- [ ] Valid menu_id exists
- [ ] Category name is unique within menu

**Item Creation:**

- [ ] Name is not empty (max 100 chars)
- [ ] Price > 0
- [ ] Valid category_id exists
- [ ] Correct item_type selected
- [ ] Required fields for item_type:
    - `recipe`: recipe_id required
    - `product`: product_id required
    - `simple`: cost required
    - `combo`: combo_items array required

---

## Quick Reference: API Endpoints

### Menus

```
GET    /api/menus                      # List all menus
GET    /api/menus/currently-available  # Active menus right now
GET    /api/menus/statistics           # Menu analytics
POST   /api/menus                      # Create menu
GET    /api/menus/{id}                 # Get menu details
PUT    /api/menus/{id}                 # Update menu
DELETE /api/menus/{id}                 # Delete menu
```

### Menu Categories

```
GET    /api/menu-categories            # List categories
POST   /api/menu-categories            # Create category
GET    /api/menu-categories/{id}       # Get category
PUT    /api/menu-categories/{id}       # Update category
DELETE /api/menu-categories/{id}       # Delete category
```

### Menu Items

```
GET    /api/menu-items/by-profitability  # Sort by profit
POST   /api/menu-items                   # Create item
GET    /api/menu-items/{id}              # Get item
PUT    /api/menu-items/{id}              # Update item
DELETE /api/menu-items/{id}              # Delete item
GET    /api/menu-items/{id}/profitability # Profit analysis
POST   /api/menu-items/{id}/toggle-availability # Toggle stock
```

---

## Complete Example: Building a Coffee Shop Menu

### Step 1: Create Menu

```bash
POST /api/menus
{
  "name": "All Day Menu",
  "description": "Our complete coffee shop menu",
  "is_active": true
}
# Returns: { "id": 1, ... }
```

### Step 2: Create Categories

```bash
# Coffee Category
POST /api/menu-categories
{
  "menu_id": 1,
  "name": "Coffee",
  "display_order": 1
}
# Returns: { "id": 1, ... }

# Pastries Category
POST /api/menu-categories
{
  "menu_id": 1,
  "name": "Pastries",
  "display_order": 2
}
# Returns: { "id": 2, ... }

# Sandwiches Category
POST /api/menu-categories
{
  "menu_id": 1,
  "name": "Sandwiches",
  "display_order": 3
}
# Returns: { "id": 3, ... }
```

### Step 3: Create Menu Items

```bash
# Recipe-based: Cappuccino
POST /api/menu-items
{
  "menu_category_id": 1,
  "name": "Cappuccino",
  "price": 4.50,
  "item_type": "recipe",
  "recipe_id": 5,
  "preparation_time_minutes": 3
}

# Product-based: Bottled Water
POST /api/menu-items
{
  "menu_category_id": 1,
  "name": "Bottled Water",
  "price": 2.00,
  "item_type": "product",
  "product_id": 42
}

# Recipe-based: Club Sandwich
POST /api/menu-items
{
  "menu_category_id": 3,
  "name": "Club Sandwich",
  "price": 8.99,
  "item_type": "recipe",
  "recipe_id": 12,
  "preparation_time_minutes": 10
}
```

### Result

```
All Day Menu
├── Coffee
│   ├── Cappuccino ($4.50)
│   └── Bottled Water ($2.00)
├── Pastries
│   └── (empty for now)
└── Sandwiches
    └── Club Sandwich ($8.99)
```

---

## Tips for Frontend Developers

### 1. Always Load Menu Hierarchy

When displaying menus, fetch the complete structure:

```javascript
GET /api/menus/{id}?include=categories.items
```

### 2. Handle Availability States

```javascript
// Check if menu is currently available
const now = new Date();
const currentTime = now.getHours() * 60 + now.getMinutes();
const fromTime = parseTime(menu.available_from_time);
const toTime = parseTime(menu.available_to_time);

const isAvailable = currentTime >= fromTime && currentTime <= toTime;
```

### 3. Visual Indicators

```javascript
// Item status badge
if (!item.is_active) {
    badge = "❌ Inactive";
} else if (!item.is_available) {
    badge = "⚠️ Out of Stock";
} else {
    badge = "✅ Available";
}
```

### 4. Cost Indicators

```javascript
// Color-code profit margins
const margin = ((item.price - item.cost) / item.price) * 100;

if (margin < 30) {
    color = "red"; // Low margin
} else if (margin < 50) {
    color = "orange"; // Medium margin
} else {
    color = "green"; // Good margin
}
```

### 5. Drag & Drop Reordering

Use `display_order` field for sorting:

```javascript
// After drag & drop, update order
items.forEach((item, index) => {
  PUT /api/menu-items/${item.id}
  { "display_order": index }
});
```

---

## Summary

✅ **3-Level Structure:** Menu → Category → Item  
✅ **4 Item Types:** Recipe, Product, Simple, Combo  
✅ **Time-Based Menus:** Control when menus are available  
✅ **Auto-Cost Calculation:** For recipe & product types  
✅ **Flexible Organization:** Categories with display order  
✅ **Real-Time Availability:** Toggle items in/out of stock

**Need Help?** Check the API documentation or contact the backend team.

---

**Document Version:** 1.0  
**Last Updated:** May 23, 2026  
**Maintained By:** Backend Development Team
