# 📦 Excel Products Import - Complete Guide

This guide explains how to import all your products from the Excel file with 5 sheets into your POS system.

## 📋 What You Get

I've created a complete import system that will:

✅ **Import all products** from your Excel file (all 5 sheets)
✅ **Create suppliers** automatically from the FRS column
✅ **Create categories** from FAMILLE and CATEGORIE columns
✅ **Create units** from UNITEE column
✅ **Create product barcodes** using the CODE column
✅ **Create purchase orders** with the quantities from QUANTITE column
✅ **Update stock levels** automatically
✅ **Create stock movements** for proper tracking
✅ **Calculate prices with TVA** included

## 📁 Files Created

### Main Seeder

- `ProductsFromExcelSeeder.php` - The main import seeder

### Helper Scripts

- `PreviewExcelImport.php` - Preview data before importing
- `SetupExcelImport.php` - Verify setup
- `ExploreExcelFile.php` - Explore file structure

### Artisan Command

- `app/Console/Commands/ImportProductsFromExcel.php` - Easy command-line import

### Documentation

- `README_PRODUCTS_FROM_EXCEL.md` - Detailed documentation
- `EXCEL_IMPORT_GUIDE.md` - This file

## 🚀 Quick Start (3 Steps)

### Step 1: Place Your Excel File

Copy your Excel file to this location:

```
database/import/la_base_de_donnes_de_nouveau_systemes_18-05.xlsx
```

**Important:** The file must be named exactly as shown above, or you can modify the seeder to use a different name.

### Step 2: Preview the Data (Recommended)

Run this command to see what will be imported:

```bash
php database/seeders/PreviewExcelImport.php
```

This will show you:

- Number of products in each sheet
- List of suppliers
- List of categories/families
- List of units
- Total inventory value
- Sample products

### Step 3: Import the Data

Choose one of these methods:

#### Method A: Using Artisan Command (Easiest)

```bash
php artisan products:import-excel
```

To preview only:

```bash
php artisan products:import-excel --preview
```

#### Method B: Using Seeder Directly

```bash
php artisan db:seed --class=ProductsFromExcelSeeder
```

#### Method C: Add to DatabaseSeeder

Add this line to `database/seeders/DatabaseSeeder.php`:

```php
$this->call([
    // ... other seeders
    ProductsFromExcelSeeder::class,
]);
```

Then run:

```bash
php artisan db:seed
```

## 📊 Excel File Format

Your Excel file should have these columns:

| Column    | Description            | Example           |
| --------- | ---------------------- | ----------------- |
| CODE      | Product code/reference | PRD001            |
| ARTICLES  | Product name           | Coca Cola 1L      |
| FRS       | Supplier name          | Supplier ABC      |
| CATEGORIE | Category               | Boissons          |
| FAMILLE   | Family (main category) | Boissons Gazeuses |
| S FAMILLE | Sub-family             | Sodas             |
| UNITEE    | Unit of measure        | Litre, Kg, Pièce  |
| PRIX UN   | Unit price (purchase)  | 15.50             |
| TVA       | Tax percentage         | 20                |
| QUANTITE  | Initial quantity       | 100               |

## 🔄 Import Process

The seeder will:

1. **Read Excel File** - All 5 sheets sequentially
2. **Create/Find Suppliers** - From FRS column (one per unique supplier)
3. **Create/Find Categories** - From FAMILLE column (main category)
4. **Create/Find Units** - From UNITEE column
5. **Create Products** with:
    - Name from ARTICLES
    - Reference from CODE
    - Purchase price from PRIX UN
    - Selling price = PRIX UN + TVA%
    - Category, Unit, Supplier linked
6. **Create Barcodes** - Using CODE as primary barcode
7. **Create Purchase Orders** - One per supplier with all their products
8. **Add Stock** - Updates product quantities and store stock
9. **Create Stock Movements** - Tracks all stock changes

## 📈 What Gets Created

### Example Output

```
Starting Excel import...
Reading Excel file: database/import/la_base_de_donnes_de_nouveau_systemes_18-05.xlsx

=== Processing Sheet #1: Produits ===
Headers: ["CODE","ARTICLES","FRS",...]
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

## 🎯 Features

### Duplicate Prevention

- Checks if products already exist by CODE (reference)
- Skips existing products
- Reuses existing suppliers, categories, and units

### Smart Defaults

- Default supplier created if FRS is empty: "Fournisseur Général"
- Default category: "Général"
- Default unit: "Unit"
- Stock alert set to 10

### Price Calculation

- Purchase price (price_buy) = PRIX UN
- Selling price (price, price_sell_1) = PRIX UN × (1 + TVA/100)
- Example: PRIX UN = 100, TVA = 20% → Selling = 120

### Purchase Orders

- Status: VALIDATED (ready to use)
- Groups products by supplier
- Reference format: IMPORT-{supplier_id}-{date}
- Due date: 30 days from import

### Stock Tracking

- Creates StockMovement records
- Type: PURCHASE
- Direction: IN
- Links to purchase order items
- Tracks previous and new stock levels

## 🛠️ Troubleshooting

### Error: "Excel file not found"

**Solution:** Place your Excel file at:

```
database/import/la_base_de_donnes_de_nouveau_systemes_18-05.xlsx
```

### Error: "No store or user found"

**Solution:** Run the main seeder first:

```bash
php artisan migrate:fresh --seed
```

### Error: "Product already exists"

**Solution:** This is normal. The seeder skips existing products to prevent duplicates.

To re-import all products:

```bash
# Clear products first (be careful!)
php artisan tinker
>>> Product::truncate();
>>> exit

# Then re-import
php artisan products:import-excel
```

### Encoding Issues (French characters)

**Solution:** Ensure your Excel file is saved with UTF-8 encoding.

### Wrong Data Imported

**Solution:**

1. Check column headers match exactly (CODE, ARTICLES, FRS, etc.)
2. Run preview first: `php database/seeders/PreviewExcelImport.php`
3. Verify data in Excel file

## 📚 Additional Resources

### View All Documentation

```bash
# Detailed README
cat database/seeders/README_PRODUCTS_FROM_EXCEL.md

# This guide
cat database/seeders/EXCEL_IMPORT_GUIDE.md
```

### Test Database Connection

```bash
php artisan tinker
>>> DB::connection()->getPdo();
>>> exit
```

### Check What's Been Imported

```bash
php artisan tinker
>>> Product::count();
>>> Suppliers::count();
>>> Category::count();
>>> OrderPurchase::count();
>>> exit
```

### View Sample Data

```bash
php artisan tinker
>>> Product::with('category', 'unit')->first();
>>> exit
```

## ⚠️ Important Notes

1. **Backup First**: Always backup your database before importing
2. **Test Environment**: Test in development environment first
3. **Preview First**: Always run preview to verify data
4. **Check Results**: Verify products after import
5. **Duplicate Prevention**: Seeder skips existing products by CODE

## 🎓 Advanced Usage

### Import from Different File

```bash
php artisan products:import-excel /path/to/your/file.xlsx
```

### Modify Import Behavior

Edit `database/seeders/ProductsFromExcelSeeder.php` to customize:

- Default stock alert level (line ~180)
- Category naming strategy (method `getOrCreateCategory`)
- Unit symbol mapping (method `getUnitSymbol`)
- Purchase order status (line ~378)
- Due date calculation (line ~388)

### Run in Production

```bash
php artisan products:import-excel --no-interaction
```

## 📞 Support

If you encounter issues:

1. Run setup verification:

    ```bash
    php database/seeders/SetupExcelImport.php
    ```

2. Check error logs:

    ```bash
    tail -f storage/logs/laravel.log
    ```

3. Verify Excel file format using preview

## ✅ Success Checklist

- [ ] Excel file placed in `database/import/`
- [ ] OpenSpout library installed (composer require openspout/openspout)
- [ ] Database migrated and seeded with basic data
- [ ] Preview run successfully
- [ ] Import completed without errors
- [ ] Products visible in system
- [ ] Stock levels correct
- [ ] Purchase orders created

---

**Created by:** GitHub Copilot
**Date:** May 24, 2026
**Version:** 1.0
