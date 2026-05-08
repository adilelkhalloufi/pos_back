<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\StoreProducts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeDuplicateProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:merge-duplicates {--dry-run : Show what would be merged without actually doing it} {--barcode= : Merge only specific barcode} {--list : Just list all duplicate barcodes without details}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find duplicate barcodes, keep product with most stock, remove barcode from others and transfer quantities';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificBarcode = $this->option('barcode');
        $listOnly = $this->option('list');

        if ($dryRun) {
            $this->warn('⚠️  DRY RUN MODE - No changes will be made');
        }

        $this->info('🔍 Searching for duplicate barcodes...');

        // Find all barcodes that appear on multiple products (including numeric duplicates with leading zeros)
        $duplicateBarcodes = DB::select("
            SELECT 
                MIN(barcode) as barcode,
                CAST(barcode AS UNSIGNED) as numeric_barcode,
                COUNT(DISTINCT product_id) as product_count
            FROM product_barcodes
            " . ($specificBarcode ? "WHERE barcode = ? OR CAST(barcode AS UNSIGNED) = CAST(? AS UNSIGNED)" : "") . "
            GROUP BY CAST(barcode AS UNSIGNED)
            HAVING COUNT(DISTINCT product_id) > 1
        ", $specificBarcode ? [$specificBarcode, $specificBarcode] : []);

        $duplicateBarcodes = collect($duplicateBarcodes);

        if ($duplicateBarcodes->isEmpty()) {
            $this->info('✅ No duplicate barcodes found!');
            return Command::SUCCESS;
        }

        $totalProducts = $duplicateBarcodes->sum('product_count');
        $this->info("Found {$duplicateBarcodes->count()} duplicate barcode(s) across {$totalProducts} products");
        $this->newLine();

        // If --list option, just show summary table
        if ($listOnly) {
            $this->showDuplicatesList($duplicateBarcodes);
            return Command::SUCCESS;
        }

        $mergedCount = 0;
        $errors = [];

        foreach ($duplicateBarcodes as $barcodeData) {
            try {
                $result = $this->mergeDuplicateBarcode($barcodeData->barcode, $dryRun);
                if ($result) {
                    $mergedCount++;
                }
            } catch (\Exception $e) {
                $errors[] = "Barcode {$barcodeData->barcode}: " . $e->getMessage();
                $this->error("❌ Error merging barcode {$barcodeData->barcode}: " . $e->getMessage());
            }
        }

        $this->newLine();
        
        if ($dryRun) {
            $this->info("📊 Summary (DRY RUN): {$mergedCount} barcode(s) would be merged");
        } else {
            $this->info("✅ Successfully merged {$mergedCount} duplicate barcode(s)");
        }

        if (!empty($errors)) {
            $this->newLine();
            $this->error('⚠️  Errors occurred:');
            foreach ($errors as $error) {
                $this->line("  - {$error}");
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Merge all products with the same barcode into one
     */
    private function mergeDuplicateBarcode(string $barcode, bool $dryRun): bool
    {
        // Get all products with this barcode (numeric comparison to catch leading zeros)
        $products = Product::whereHas('barcodes', function ($query) use ($barcode) {
            $query->whereRaw('CAST(' . ProductBarcode::COL_BARCODE . ' AS UNSIGNED) = CAST(? AS UNSIGNED)', [$barcode]);
        })->with(['barcodes', 'store'])->get();

        if ($products->count() < 2) {
            return false;
        }

        // Get all barcode variations (e.g., 085715320124 and 85715320124)
        $barcodeVariations = ProductBarcode::whereRaw('CAST(' . ProductBarcode::COL_BARCODE . ' AS UNSIGNED) = CAST(? AS UNSIGNED)', [$barcode])
            ->distinct()
            ->pluck(ProductBarcode::COL_BARCODE)
            ->toArray();

        // Sort by total stock (descending) - keep the one with most stock
        $products = $products->sortByDesc(function ($product) {
            return $product->store->sum(StoreProducts::COL_STOCK);
        })->values();

        $displayBarcode = count($barcodeVariations) > 1 
            ? implode(' / ', $barcodeVariations) . " <fg=red>(Leading Zero Issue)</>"
            : $barcode;

        $this->line("📦 Barcode: <fg=cyan>{$displayBarcode}</>");
        $this->line("   Found <fg=yellow>{$products->count()}</> duplicate products:");
        $this->newLine();

        $keepProduct = $products->first();
        $duplicates = $products->slice(1);
        
        // Show which product will keep the barcode
        $keepStock = $keepProduct->store->sum(StoreProducts::COL_STOCK);
        
        // Find which barcode variation(s) the kept product has
        $keepProductBarcodeVariations = $keepProduct->barcodes
            ->whereIn('barcode', $barcodeVariations)
            ->pluck('barcode')
            ->toArray();
        
        $this->line("   <fg=green>✓ KEEP BARCODE:</> Product #<fg=green>{$keepProduct->id}</>");
        $this->line("     Name: <fg=white>{$keepProduct->name}</>");
        $this->line("     Current Stock: <fg=green>{$keepStock}</>");
        if (!empty($keepProductBarcodeVariations)) {
            $this->line("     Has Barcode(s): <fg=cyan>" . implode(', ', $keepProductBarcodeVariations) . "</>");
        }
        
        // Show store-by-store breakdown for kept product
        foreach ($keepProduct->store as $storeProduct) {
            if ($storeProduct->stock > 0) {
                $this->line("       • Store #{$storeProduct->store_id}: {$storeProduct->stock}");
            }
        }
        $this->newLine();

        // Calculate total stock to be transferred
        $totalTransferStock = 0;
        foreach ($duplicates as $duplicate) {
            $totalTransferStock += $duplicate->store->sum(StoreProducts::COL_STOCK);
        }

        // Show products that will lose the barcode
        $this->line("   <fg=red>→ REMOVE BARCODE FROM:</>");
        foreach ($duplicates as $index => $duplicate) {
            $dupStock = $duplicate->store->sum(StoreProducts::COL_STOCK);
            
            // Find which barcode variation(s) this product has
            $productBarcodeVariations = $duplicate->barcodes
                ->whereIn('barcode', $barcodeVariations)
                ->pluck('barcode')
                ->toArray();
            
            $this->line("     " . ($index + 1) . ". Product #<fg=red>{$duplicate->id}</>");
            $this->line("        Name: <fg=white>{$duplicate->name}</>");
            $this->line("        Current Stock: <fg=yellow>{$dupStock}</>");
            if (!empty($productBarcodeVariations)) {
                $this->line("        Has Barcode(s): <fg=cyan>" . implode(', ', $productBarcodeVariations) . "</>");
            }
            
            // Show store-by-store breakdown
            foreach ($duplicate->store as $storeProduct) {
                if ($storeProduct->stock > 0) {
                    $this->line("          • Store #{$storeProduct->store_id}: {$storeProduct->stock} <fg=cyan>→ will transfer to Product #{$keepProduct->id}</>");
                }
            }
        }
        
        $this->newLine();
        $this->line("   <fg=magenta>SUMMARY:</>");
        $summaryBarcode = count($barcodeVariations) > 1 
            ? "barcode variations (" . implode(', ', $barcodeVariations) . ")"
            : "barcode {$barcode}";
        $this->line("   • Product #<fg=green>{$keepProduct->id}</> will keep {$summaryBarcode}");
        $this->line("   • Product #<fg=green>{$keepProduct->id}</> final stock: <fg=green>" . ($keepStock + $totalTransferStock) . "</> (current: {$keepStock} + transferred: {$totalTransferStock})");
        $this->line("   • <fg=yellow>{$duplicates->count()}</> product(s) will have barcode removed but remain in system");

        if ($dryRun) {
            $this->newLine();
            $this->warn("   [DRY RUN] No changes made");
            $this->newLine();
            return true;
        }

        return DB::transaction(function () use ($products, $barcode, $keepProduct, $barcodeVariations) {
            $duplicates = $products->slice(1);

            $this->newLine();
            $this->line("   <fg=green>Processing...</>");

            foreach ($duplicates as $duplicate) {
                // 1. Transfer stock from duplicate to kept product
                $this->transferStock($duplicate, $keepProduct);

                // 2. Remove ALL barcode variations from duplicate product (handles leading zero issues)
                foreach ($barcodeVariations as $variation) {
                    ProductBarcode::where(ProductBarcode::COL_PRODUCT_ID, $duplicate->id)
                        ->whereRaw('CAST(' . ProductBarcode::COL_BARCODE . ' AS UNSIGNED) = CAST(? AS UNSIGNED)', [$variation])
                        ->delete();
                }

                $this->line("   ✓ Removed barcode variations from Product #{$duplicate->id} and transferred stock");
            }

            $this->info("   ✅ Successfully merged barcode into Product #{$keepProduct->id}");
            $this->newLine();

            return true;
        });
    }

    /**
     * Transfer stock from duplicate product to kept product
     */
    private function transferStock(Product $fromProduct, Product $toProduct): void
    {
        // Get all store stocks for the duplicate product
        $storeStocks = StoreProducts::where(StoreProducts::COL_PRODUCT_ID, $fromProduct->id)->get();

        foreach ($storeStocks as $storeStock) {
            // Find or create store product for the kept product
            $targetStoreProduct = StoreProducts::firstOrCreate(
                [
                    StoreProducts::COL_STORE_ID => $storeStock->store_id,
                    StoreProducts::COL_PRODUCT_ID => $toProduct->id,
                ],
                [
                    StoreProducts::COL_STOCK => 0,
                    StoreProducts::COL_PRICE => $toProduct->price,
                    StoreProducts::COL_COST => $toProduct->price_buy,
                ]
            );

            // Add the stock
            $targetStoreProduct->stock += $storeStock->stock;
            $targetStoreProduct->save();

            // Delete the duplicate's store product
            $storeStock->delete();
        }
    }

    /**
     * Show a summary list of all duplicate barcodes
     */
    private function showDuplicatesList($duplicateBarcodes): void
    {
        $this->line('📋 <fg=cyan>LIST OF ALL DUPLICATE BARCODES</>');
        $this->newLine();

        $tableData = [];
        $index = 1;

        foreach ($duplicateBarcodes as $barcodeData) {
            // Get all products with this barcode
            $products = Product::whereHas('barcodes', function ($query) use ($barcodeData) {
                $query->where(ProductBarcode::COL_BARCODE, $barcodeData->barcode);
            })->with('store')->get();

            // Sort by stock descending
            $products = $products->sortByDesc(function ($product) {
                return $product->store->sum(StoreProducts::COL_STOCK);
            })->values();

            $this->line("$index. <fg=cyan>Barcode: {$barcodeData->barcode}</> ({$products->count()} products)");
            
            foreach ($products as $prodIndex => $product) {
                $stock = $product->store->sum(StoreProducts::COL_STOCK);
                $icon = $prodIndex === 0 ? '<fg=green>✓ KEEP</>' : '<fg=red>✗ REMOVE</>';
                $this->line("   {$icon} Product #{$product->id}: {$product->name} (Stock: {$stock})");
            }
            
            $this->newLine();
            $index++;
        }

        $this->info("💡 Tip: Run without --list to see detailed breakdown");
        $this->info("💡 Tip: Add --dry-run to preview changes without applying them");
        $this->info("💡 Tip: Use --barcode=XXXXX to process only one specific barcode");
    }


}
