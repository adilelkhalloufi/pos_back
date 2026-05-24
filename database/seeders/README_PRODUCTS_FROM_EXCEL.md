# Products From Excel Seeder

This seeder imports products and creates purchase orders from an Excel file with 5 sheets.

## Excel File Format

The Excel file should have the following columns:

- **CODE**: Product code/reference (unique identifier)
- **ARTICLES**: Product name
- **FRS**: Supplier name (Fournisseur)
- **CATEGORIE**: Category name
- **FAMILLE**: Family/main category name
- **S FAMILLE**: Sub-family name
- **UNITEE**: Unit of measure (kg, litre, pièce, etc.)
- **PRIX UN**: Unit price (purchase price)
- **TVA**: Tax/VAT percentage
- **QUANTITE**: Initial quantity for purchase order

## Setup Instructions

### 1. Place the Excel File

Copy your Excel file to:

```
database/import/la_base_de_donnes_de_nouveau_systemes_18-05.xlsx
```

### 2. Ensure Prerequisites

Make sure you have:

- At least one Store created
- At least one User created
- At least one Payment Method created

These are usually created by the main DatabaseSeeder.

### 3. Run the Seeder

Run the seeder with:

```bash
php artisan db:seed --class=ProductsFromExcelSeeder
```

Or include it in your DatabaseSeeder.php:

```php
$this->call([
    // ... other seeders
    ProductsFromExcelSeeder::class,
]);
```

## What the Seeder Does

### 1. Creates Products

- Creates a product for each row in the Excel file
- Uses CODE as the product reference
- Calculates selling price by adding TVA to unit price
- Sets purchase price (price_buy) from PRIX UN
- Creates product with proper category and unit

### 2. Creates Suppliers

- Automatically creates suppliers from the FRS column
- Groups products by supplier
- Creates default supplier if none specified

### 3. Creates Categories

- Creates categories from FAMILLE and CATEGORIE columns
- Uses FAMILLE as primary category if available
- Stores full hierarchy in description

### 4. Creates Units

- Creates units from UNITEE column
- Automatically determines appropriate symbols
- Supports common units: kg, g, L, mL, pièce, etc.

### 5. Creates Barcodes

- Creates primary barcode using product CODE
- Links barcode to product

### 6. Creates Store-Product Relationships

- Links products to store
- Initializes stock to 0
- Sets prices and costs

### 7. Creates Purchase Orders

- Groups products by supplier
- Creates validated purchase orders for each supplier
- Creates purchase order items with quantities from Excel
- Updates product and store stock with quantities

## Features

### Error Handling

- Skips empty rows
- Handles missing data gracefully
- Reports errors without stopping import
- Shows summary of errors at end

### Duplicate Prevention

- Checks for existing products by reference code
- Skips products that already exist
- Reuses existing suppliers, categories, and units

### Progress Tracking

- Shows progress every 50 products
- Displays sheet names being processed
- Shows final statistics

### Transaction Safety

- Wraps entire import in database transaction
- Rolls back on any error
- Ensures data consistency

## Output Example

```
Starting Excel import...
Reading Excel file: database/import/la_base_de_donnes_de_nouveau_systemes_18-05.xlsx

=== Processing Sheet #1: Produits ===
Headers: ["CODE","ARTICLES","FRS","CATEGORIE","FAMILLE","S FAMILLE","UNITEE","PRIX UN","TVA","QUANTITE"]
Processed 50 products...
Processed 100 products...

=== Processing Sheet #2: Boissons ===
...

========================================
Import completed successfully!
========================================
Products created: 487
Purchase orders created: 23
Suppliers: 15
Categories: 32
Units: 18
```

## Troubleshooting

### "Excel file not found"

- Ensure the file is in `database/import/` folder
- Check the filename matches exactly

### "No store or user found"

- Run the main DatabaseSeeder first:
    ```bash
    php artisan db:seed
    ```

### "Product already exists"

- The seeder skips existing products
- Delete existing products if you want to re-import

### Encoding Issues

- Ensure Excel file is saved in UTF-8 encoding
- French characters (é, è, à, etc.) should display correctly

## Data Mapping

| Excel Column  | Product Field                     | Notes                  |
| ------------- | --------------------------------- | ---------------------- |
| CODE          | reference, supplier_code, barcode | Unique identifier      |
| ARTICLES      | name                              | Product name           |
| PRIX UN       | price_buy                         | Purchase price         |
| PRIX UN + TVA | price, price_sell_1               | Selling price          |
| CATEGORIE     | category description              | Used for hierarchy     |
| FAMILLE       | category name                     | Main category          |
| S FAMILLE     | category description              | Sub-category info      |
| UNITEE        | unit_id                           | Creates/links unit     |
| FRS           | supplier                          | Creates/links supplier |
| QUANTITE      | Initial stock                     | Via purchase order     |

## Notes

- All products are created as active and stockable
- Stock alert is set to 10 by default
- Purchase orders are created with VALIDATED status
- Due date is set to 30 days from import
- Categories use FAMILLE as main category name
- Sub-family info is stored in category description
