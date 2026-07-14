<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Store;
use App\Models\Unit;
use App\Models\UnitConversion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use OpenSpout\Reader\XLSX\Reader;

class RecipeIngredientsSeeder extends Seeder
{
    private $store;
    private $user;
    private $units = [];
    private $products = [];
    private $recipes = [];
    private $errors = [];
    private $stats = [
        'recipes_created' => 0,
        'ingredients_added' => 0,
        'units_created' => 0,
        'conversions_created' => 0,
        'rows_processed' => 0,
        'rows_skipped' => 0,
    ];

    public function run(): void
    {
        $this->command->info('🚀 Starting Recipe & Ingredients Import from Excel...');

        // Get store and user
        $this->store = Store::first();
        $this->user = User::first();

        if (!$this->store || !$this->user) {
            $this->command->error('❌ No store or user found. Please run DatabaseSeeder first.');
            return;
        }

        $this->command->info("✅ Using Store: {$this->store->name} (ID: {$this->store->id})");
        $this->command->info("✅ Using User: {$this->user->name} (ID: {$this->user->id})");

        // Load existing data into memory
        $this->loadUnits();
        $this->loadProducts();

        // Path to Excel file
        $excelFile = database_path('import/data.xlsx');

        if (!file_exists($excelFile)) {
            $this->command->error("❌ Excel file not found: $excelFile");
            $this->command->info('📝 Please place the Excel file at: database/import/fich ingrediant.xlsx');
            return;
        }

        $this->command->info("📖 Reading Excel file...\n");

        DB::beginTransaction();

        try {
            $reader = new Reader();
            $reader->open($excelFile);

            foreach ($reader->getSheetIterator() as $sheet) {
                $sheetName = $sheet->getName();
                $this->command->info("📄 Processing Sheet: {$sheetName}");
                $this->command->info(str_repeat('=', 80));

                $this->processSheet($sheet);
            }

            $reader->close();

            // Create basic unit conversions
            $this->createBasicUnitConversions();

            DB::commit();

            $this->printSummary();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("\n❌ ERROR: " . $e->getMessage());
            $this->command->error("Stack trace: " . $e->getTraceAsString());
        }
    }

    private function loadUnits(): void
    {
        $units = Unit::all();
        foreach ($units as $unit) {
            $key = strtolower(trim($unit->symbol));
            $this->units[$key] = $unit;
            $this->units[strtolower(trim($unit->name))] = $unit;
        }
        $this->command->info("✅ Loaded " . count($units) . " units");
    }

    private function loadProducts(): void
    {
        $products = Product::all();
        foreach ($products as $product) {
            if ($product->reference) {
                $this->products['ref_' . strtolower(trim($product->reference))] = $product;
            }
            $this->products['name_' . strtolower(trim($product->name))] = $product;
        }
        $this->command->info("✅ Loaded " . count($products) . " products\n");
    }

    private function processSheet($sheet): void
    {
        $headers = [];
        $rowIndex = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $rowIndex++;
            $cells = $row->getCells();
            $values = array_map(fn($cell) => $cell->getValue(), $cells);

            // First row is header
            if ($rowIndex === 1) {
                $headers = array_map(fn($h) => strtolower(trim($h ?? '')), $values);
                $this->command->info("📋 Headers: " . implode(', ', array_filter($headers)));
                continue;
            }

            // Skip empty rows
            if ($this->isEmptyRow($values)) {
                continue;
            }

            // Process data row
            try {
                $this->processRecipeRow($headers, $values, $rowIndex);
                $this->stats['rows_processed']++;
            } catch (\Exception $e) {
                $this->errors[] = "Row {$rowIndex}: " . $e->getMessage();
                $this->stats['rows_skipped']++;
                $this->command->warn("⚠️  Row {$rowIndex}: " . $e->getMessage());
            }
        }
    }

    private function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if (!empty(trim($value ?? ''))) {
                return false;
            }
        }
        return true;
    }

    private function processRecipeRow(array $headers, array $values, int $rowIndex): void
    {
        // Create associative array
        $data = [];
        foreach ($headers as $index => $header) {
            $data[$header] = $values[$index] ?? null;
        }

        // Extract recipe information using flexible column matching
        $recipeName = $this->findValue($data, ['recipe', 'recette', 'nom recette', 'recipe name', 'plat', 'dish', 'fiche']);
        $productName = $this->findValue($data, ['ingredient', 'produit', 'product', 'nom produit', 'product name', 'designation', 'article']);
        $productRef = $this->findValue($data, ['reference', 'ref', 'code', 'sku', 'code produit']);
        $quantity = $this->findValue($data, ['quantity', 'quantite', 'quantité', 'qty', 'qte']);
        $unitSymbol = $this->findValue($data, ['unit', 'unite', 'unité', 'um', 'u.m.', 'conditionnement']);
        $wastePercent = $this->findValue($data, ['waste', 'perte', 'déchet', 'waste %', 'perte %']) ?? 0;
        $notes = $this->findValue($data, ['notes', 'note', 'preparation', 'préparation', 'instruction', 'remarque']);

        // Validate required fields
        if (empty($recipeName)) {
            throw new \Exception("Recipe name is required");
        }

        if (empty($productName) && empty($productRef)) {
            throw new \Exception("Product name or reference is required");
        }

        // Clean quantity
        $quantity = $this->cleanNumber($quantity);
        if (empty($quantity) || !is_numeric($quantity)) {
            throw new \Exception("Valid quantity is required (got: {$quantity})");
        }

        // Clean waste percentage
        $wastePercent = $this->cleanNumber($wastePercent);

        // Find or create recipe
        $recipe = $this->findOrCreateRecipe($recipeName);

        // Find product
        $product = $this->findProduct($productName, $productRef);
        if (!$product) {
            throw new \Exception("Product not found: " . ($productName ?: $productRef));
        }

        // Find or create unit
        $unit = $this->findOrCreateUnit($unitSymbol, $product);

        // Create recipe ingredient
        $this->createRecipeIngredient($recipe, $product, (float)$quantity, $unit, (float)$wastePercent, $notes);

        $this->command->info("  ✓ {$recipe->name} + {$product->name} ({$quantity} {$unit->symbol})");
    }

    private function findValue(array $data, array $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            $normalizedKey = strtolower(trim($key));
            if (isset($data[$normalizedKey]) && !empty(trim($data[$normalizedKey] ?? ''))) {
                return trim($data[$normalizedKey]);
            }
        }
        return null;
    }

    private function cleanNumber($value): string
    {
        if (empty($value)) {
            return '0';
        }
        // Remove any non-numeric characters except comma and dot
        $cleaned = preg_replace('/[^0-9.,]/', '', $value);
        // Replace comma with dot for decimal
        $cleaned = str_replace(',', '.', $cleaned);
        return $cleaned;
    }

    private function findOrCreateRecipe(string $recipeName): Recipe
    {
        $recipeKey = strtolower(trim($recipeName));

        if (!isset($this->recipes[$recipeKey])) {
            $recipe = Recipe::where('name', $recipeName)
                ->where('store_id', $this->store->id)
                ->first();

            if (!$recipe) {
                // Get default serving unit
                $servingUnit = $this->units['un'] ?? $this->units['ut'] ?? $this->units['pc'] ?? Unit::first();

                $recipe = Recipe::create([
                    'name' => $recipeName,
                    'description' => "Imported from Excel",
                    'yield_quantity' => 1,
                    'yield_unit_id' => $servingUnit?->id,
                    'is_active' => true,
                    'store_id' => $this->store->id,
                    'user_id' => $this->user->id,
                ]);

                $this->stats['recipes_created']++;
                $this->command->info("  ✨ Created recipe: {$recipeName}");
            }

            $this->recipes[$recipeKey] = $recipe;
        }

        return $this->recipes[$recipeKey];
    }

    private function findProduct(?string $name, ?string $reference): ?Product
    {
        // Try by reference first (most reliable)
        if ($reference) {
            $key = 'ref_' . strtolower(trim($reference));
            if (isset($this->products[$key])) {
                return $this->products[$key];
            }
        }

        // Try by name
        if ($name) {
            $key = 'name_' . strtolower(trim($name));
            if (isset($this->products[$key])) {
                return $this->products[$key];
            }
        }

        return null;
    }

    private function findOrCreateUnit(?string $symbol, Product $product): Unit
    {
        // If no symbol provided, use product's unit or default to kg
        if (empty($symbol)) {
            if ($product->unit_id) {
                return Unit::find($product->unit_id);
            }
            $symbol = 'kg';
        }

        // Normalize unit symbol
        $symbol = $this->normalizeUnitSymbol($symbol);
        $key = strtolower(trim($symbol));

        // Try to find existing unit
        if (isset($this->units[$key])) {
            return $this->units[$key];
        }

        // Create new unit if not found
        $unit = Unit::create([
            'name' => ucfirst($symbol),
            'symbol' => $symbol,
            'description' => "Imported from Excel",
            'is_active' => true,
            'store_id' => $this->store->id,
        ]);

        $this->units[$key] = $unit;
        $this->stats['units_created']++;
        $this->command->info("  ✨ Created unit: {$symbol}");

        return $unit;
    }

    private function normalizeUnitSymbol(string $unit): string
    {
        $unit = strtolower(trim($unit));

        $unitMap = [
            // Weight
            'kilogramme' => 'kg', 'kilo' => 'kg', 'kilos' => 'kg',
            'gramme' => 'g', 'grammes' => 'g', 'gr' => 'g',
            // Volume
            'litre' => 'lt', 'litres' => 'lt', 'l' => 'lt',
            'millilitre' => 'ml', 'millilitres' => 'ml',
            // Count
            'piece' => 'un', 'pièce' => 'un', 'pieces' => 'un', 'pièces' => 'un',
            'pc' => 'un', 'unite' => 'un', 'unité' => 'un', 'ut' => 'un',
            // Packaging
            'bouteille' => 'bt', 'bouteilles' => 'bt',
            'paquet' => 'pq', 'paquets' => 'pq', 'boite' => 'pq', 'boîte' => 'pq',
            'sac' => 'paq', 'sacs' => 'paq',
        ];

        return $unitMap[$unit] ?? $unit;
    }

    private function createRecipeIngredient(
        Recipe $recipe,
        Product $product,
        float $quantity,
        Unit $unit,
        float $wastePercent,
        ?string $notes
    ): void {
        // Check if already exists
        $existing = RecipeIngredient::where('recipe_id', $recipe->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            // Update existing
            $existing->update([
                'quantity' => $quantity,
                'unit_id' => $unit->id,
                'waste_percentage' => $wastePercent,
                'preparation_note' => $notes,
            ]);
        } else {
            // Create new
            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_id' => $unit->id,
                'waste_percentage' => $wastePercent,
                'preparation_note' => $notes,
                'is_optional' => false,
                'cost' => 0, // Will be calculated later
            ]);

            $this->stats['ingredients_added']++;
        }
    }

    private function createBasicUnitConversions(): void
    {
        $conversions = [
            ['from' => 'kg', 'to' => 'g', 'factor' => 1000],
            ['from' => 'g', 'to' => 'kg', 'factor' => 0.001],
            ['from' => 'lt', 'to' => 'ml', 'factor' => 1000],
            ['from' => 'ml', 'to' => 'lt', 'factor' => 0.001],
            ['from' => 'l', 'to' => 'ml', 'factor' => 1000],
            ['from' => 'ml', 'to' => 'l', 'factor' => 0.001],
        ];

        foreach ($conversions as $conv) {
            $fromUnit = $this->units[strtolower($conv['from'])] ?? null;
            $toUnit = $this->units[strtolower($conv['to'])] ?? null;

            if ($fromUnit && $toUnit) {
                $exists = UnitConversion::where('from_unit_id', $fromUnit->id)
                    ->where('to_unit_id', $toUnit->id)
                    ->exists();

                if (!$exists) {
                    UnitConversion::create([
                        'from_unit_id' => $fromUnit->id,
                        'to_unit_id' => $toUnit->id,
                        'conversion_factor' => $conv['factor'],
                        'store_id' => $this->store->id,
                    ]);

                    $this->stats['conversions_created']++;
                }
            }
        }
    }

    private function printSummary(): void
    {
        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("✅ IMPORT COMPLETED SUCCESSFULLY!");
        $this->command->info(str_repeat('=', 80));
        $this->command->info("📊 STATISTICS:");
        $this->command->info("  - Recipes created: {$this->stats['recipes_created']}");
        $this->command->info("  - Ingredients added: {$this->stats['ingredients_added']}");
        $this->command->info("  - Units created: {$this->stats['units_created']}");
        $this->command->info("  - Unit conversions created: {$this->stats['conversions_created']}");
        $this->command->info("  - Rows processed: {$this->stats['rows_processed']}");
        $this->command->info("  - Rows skipped: {$this->stats['rows_skipped']}");

        if (count($this->errors) > 0) {
            $this->command->warn("\n⚠️  ERRORS: " . count($this->errors));
            foreach (array_slice($this->errors, 0, 10) as $error) {
                $this->command->warn("  - {$error}");
            }
            if (count($this->errors) > 10) {
                $this->command->warn("  ... and " . (count($this->errors) - 10) . " more");
            }
        }

        $this->command->info("\n🎉 Done!");
    }
}
