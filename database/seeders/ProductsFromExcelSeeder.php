<?php

namespace Database\Seeders;

use App\Enums\EnumOrderStatue;
use App\Models\Category;
use App\Models\ModePayemnt;
use App\Models\OrderPurchase;
use App\Models\OrderPurchaseItems;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StoreProducts;
use App\Models\Suppliers;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenSpout\Reader\XLSX\Reader;

class ProductsFromExcelSeeder extends Seeder
{
    private $store;
    private $user;
    private $paymentMethod;
    private $suppliers = [];
    private $categories = [];
    private $units = [];
    private $families = [];
    private $subFamilies = [];
    private $productCount = 0;
    private $purchaseCount = 0;
    private $errors = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting Excel import...');

        // Get first store and user
        $this->store = Store::first();
        $this->user = User::first();

        if (!$this->store || !$this->user) {
            $this->command->error('No store or user found. Please run DatabaseSeeder first.');
            return;
        }

        // Get or create payment method
        $this->paymentMethod = ModePayemnt::first();
        if (!$this->paymentMethod) {
            $this->command->error('No payment method found. Please create one first.');
            return;
        }

        $excelFile = database_path('import/database.xlsx');

        if (!file_exists($excelFile)) {
            $this->command->error("Excel file not found: $excelFile");
            $this->command->info('Please place the Excel file at: database/import/database.xlsx');
            return;
        }

        $this->command->info("Reading Excel file: $excelFile");

        DB::beginTransaction();

        try {
            $reader = new Reader();
            $reader->open($excelFile);

            $sheetIndex = 0;
            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetIndex++;
                $sheetName = $sheet->getName();

                $this->command->info("\n=== Processing Sheet #{$sheetIndex}: {$sheetName} ===");

                $this->processSheet($sheet, $sheetName);
            }

            $reader->close();

            DB::commit();

            $this->command->info("\n========================================");
            $this->command->info("Import completed successfully!");
            $this->command->info("========================================");
            $this->command->info("Products created: {$this->productCount}");
            $this->command->info("Purchase orders created: {$this->purchaseCount}");
            $this->command->info("Suppliers: " . count($this->suppliers));
            $this->command->info("Categories: " . count($this->categories));
            $this->command->info("Units: " . count($this->units));

            if (count($this->errors) > 0) {
                $this->command->warn("\nErrors encountered: " . count($this->errors));
                foreach (array_slice($this->errors, 0, 10) as $error) {
                    $this->command->warn("  - $error");
                }
                if (count($this->errors) > 10) {
                    $this->command->warn("  ... and " . (count($this->errors) - 10) . " more errors");
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error during import: " . $e->getMessage());
            $this->command->error($e->getTraceAsString());
        }
    }

    private function processSheet($sheet, $sheetName): void
    {
        $rowIndex = 0;
        $headers = [];

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex++;
            $cells = $row->getCells();
            $data = array_map(fn($cell) => $cell->getValue(), $cells);

            // First row is header
            if ($rowIndex === 1) {
                $headers = $data;
                $this->command->info("Headers: " . json_encode($headers, JSON_UNESCAPED_UNICODE));
                continue;
            }

            // Skip empty rows
            if (empty(array_filter($data))) {
                continue;
            }

            try {
                // Map data to associative array
                $rowData = [];
                foreach ($headers as $index => $header) {
                    $rowData[$header] = $data[$index] ?? null;
                }

                $this->processRow($rowData, $sheetName, $rowIndex);
            } catch (\Exception $e) {
                $error = "Sheet '{$sheetName}' Row {$rowIndex}: " . $e->getMessage();
                $this->errors[] = $error;
                $this->command->warn($error);
            }
        }
    }

    private function processRow(array $rowData, string $sheetName, int $rowIndex): void
    {
        // Extract data from row
        $code = trim($rowData['CODE'] ?? '');
        $articleName = trim($rowData['ARTICLES'] ?? '');
        $supplierName = trim($rowData['FRS'] ?? ''); // Fournisseur (Supplier)
        $categoryName = trim($rowData['CATEGORIE'] ?? '');
        $familyName = trim($rowData['FAMILLE'] ?? '');
        $subFamilyName = trim($rowData['S FAMILLE'] ?? '');
        $unitName = trim($rowData['UNITEE'] ?? '');
        $unitPrice = $this->parseFloat($rowData['PRIX UN'] ?? 0);
        $tva = $this->parseFloat($rowData['TVA'] ?? 0);
        $quantity = $this->parseFloat($rowData['QUANTITE'] ?? 0);

        // Skip if no product name or code
        if (empty($articleName) || empty($code)) {
            return;
        }

        // Get or create supplier
        $supplier = $this->getOrCreateSupplier($supplierName);

        // Get or create category
        $category = $this->getOrCreateCategory($categoryName, $familyName, $subFamilyName);

        // Get or create unit
        $unit = $this->getOrCreateUnit($unitName);

        // Check if product already exists
        $existingProduct = Product::where('reference', $code)
            ->where('store_id', $this->store->id)
            ->first();

        if ($existingProduct) {
            $this->command->warn("Product {$code} already exists, skipping...");
            return;
        }

        // Calculate selling price (adding VAT)
        $priceWithTax = $unitPrice * (1 + ($tva / 100));

        // Create product
        $product = Product::create([
            'name' => $articleName,
            'reference' => $code,
            'supplier_code' => $code,
            'description' => "Famille: {$familyName}, S-Famille: {$subFamilyName}",
            'price' => round($priceWithTax, 2),
            'price_sell_1' => round($priceWithTax, 2),
            'price_buy' => round($unitPrice, 2),
            'stock_alert' => 10,
            'is_active' => true,
            'is_stockable' => true,
            'archive' => false,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
            'user_id' => $this->user->id,
            'store_id' => $this->store->id,
        ]);

        $this->productCount++;

        // Create primary barcode with product code
        ProductBarcode::create([
            'product_id' => $product->id,
            'barcode' => $code,
            'is_primary' => true,
        ]);

        // Create store-product relationship
        StoreProducts::create([
            'store_id' => $this->store->id,
            'product_id' => $product->id,
            'price' => $product->price,
            'cost' => $product->price_buy,
            'stock' => 0, // Will be updated by purchase order
        ]);

        // Create purchase order if quantity > 0
        if ($quantity > 0 && $supplier) {
            $this->createPurchaseOrder($product, $supplier, $quantity, $unitPrice);
        }

        if ($this->productCount % 50 == 0) {
            $this->command->info("Processed {$this->productCount} products...");
        }
    }

    private function getOrCreateSupplier(?string $supplierName): ?Suppliers
    {
        if (empty($supplierName)) {
            // Create default supplier if none specified
            $supplierName = 'Fournisseur Général';
        }

        $key = strtolower(trim($supplierName));

        if (isset($this->suppliers[$key])) {
            return $this->suppliers[$key];
        }

        // Try to find existing supplier
        $supplier = Suppliers::where('company_name', $supplierName)
            ->where('store_id', $this->store->id)
            ->first();

        if (!$supplier) {
            $supplier = Suppliers::create([
                'first_name' => '',
                'last_name' => '',
                'company_name' => $supplierName,
                'email' => Str::slug($supplierName) . '@supplier.com',
                'phone' => '0000000000',
                'address' => '',
                'city' => '',
                'country' => 'Morocco',
                'zip_code' => '',
                'user_id' => $this->user->id,
                'store_id' => $this->store->id,
            ]);
        }

        $this->suppliers[$key] = $supplier;
        return $supplier;
    }

    private function getOrCreateCategory(string $categoryName, string $familyName, string $subFamilyName): Category
    {
        // Use family as main category, or category if family is empty
        $mainCategoryName = !empty($familyName) ? $familyName : (!empty($categoryName) ? $categoryName : 'Général');

        $key = strtolower(trim($mainCategoryName));

        if (isset($this->categories[$key])) {
            return $this->categories[$key];
        }

        // Try to find existing category
        $category = Category::where('name', $mainCategoryName)
            ->where('store_id', $this->store->id)
            ->first();

        if (!$category) {
            $category = Category::create([
                'name' => $mainCategoryName,
                'slug' => Str::slug($mainCategoryName),
                'description' => "Catégorie: {$categoryName}, Famille: {$familyName}, S-Famille: {$subFamilyName}",
                'display_order' => count($this->categories) + 1,
                'user_id' => $this->user->id,
                'store_id' => $this->store->id,
            ]);
        }

        $this->categories[$key] = $category;
        return $category;
    }

    private function getOrCreateUnit(?string $unitName): ?Unit
    {
        if (empty($unitName)) {
            $unitName = 'Unit';
        }

        $key = strtolower(trim($unitName));

        if (isset($this->units[$key])) {
            return $this->units[$key];
        }

        // Try to find existing unit
        $unit = Unit::where('name', $unitName)
            ->where('store_id', $this->store->id)
            ->first();

        if (!$unit) {
            // Try to determine symbol from unit name
            $symbol = $this->getUnitSymbol($unitName);

            $unit = Unit::create([
                'name' => $unitName,
                'symbol' => $symbol,
                'description' => '',
                'is_active' => true,
                'store_id' => $this->store->id,
            ]);
        }

        $this->units[$key] = $unit;
        return $unit;
    }

    private function getUnitSymbol(string $unitName): string
    {
        $unitName = strtolower($unitName);

        $symbols = [
            'kg' => 'kg',
            'kilogramme' => 'kg',
            'gram' => 'g',
            'gramme' => 'g',
            'litre' => 'L',
            'liter' => 'L',
            'millilitre' => 'mL',
            'milliliter' => 'mL',
            'piece' => 'pc',
            'pièce' => 'pc',
            'unit' => 'unit',
            'unité' => 'unit',
            'boite' => 'box',
            'box' => 'box',
            'carton' => 'carton',
            'sachet' => 'sachet',
            'paquet' => 'pqt',
        ];

        foreach ($symbols as $key => $symbol) {
            if (str_contains($unitName, $key)) {
                return $symbol;
            }
        }

        // Default: use first 3 chars as symbol
        return substr($unitName, 0, 3);
    }

    private function createPurchaseOrder(Product $product, Suppliers $supplier, float $quantity, float $unitPrice): void
    {
        // Find or create purchase order for this supplier
        $orderReference = "IMPORT-{$supplier->id}-" . date('Y-m-d');

        $purchaseOrder = OrderPurchase::firstOrCreate(
            [
                'reference' => $orderReference,
                'supplier_id' => $supplier->id,
                'store_id' => $this->store->id,
            ],
            [
                'order_number' => 'Brouillon',
                'status' => EnumOrderStatue::PENDING->value,
                'paid_method_id' => $this->paymentMethod->id,
                'user_id' => $this->user->id,
                'public_note' => 'Import from Excel - ' . date('Y-m-d H:i:s'),
                'private_note' => 'Automatic import - Not validated yet',
                'discount' => 0,
                'due_date' => now()->addDays(30),
            ]
        );

        if ($purchaseOrder->wasRecentlyCreated) {
            $this->purchaseCount++;
        }

        // Create purchase order item (stock will be added when order is validated)
        $total = $quantity * $unitPrice;

        OrderPurchaseItems::create([
            'order_id' => $purchaseOrder->id,
            'product_id' => $product->id,
            'store_id' => $this->store->id,
            'name' => $product->name,
            'quantity' => $quantity,
            'price' => round($unitPrice, 2),
            'total' => round($total, 2),
        ]);

        // Note: Stock is NOT updated here - it will be updated when the purchase order is validated
    }

    private function parseFloat($value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        // Remove any non-numeric characters except . and -
        $cleaned = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return (float) $cleaned;
    }
}
