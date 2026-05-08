<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\StoreProducts;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDuplicateBarcodes extends Command
{
    protected $signature = 'products:check-barcode {barcode? : The barcode to check} {--all : Find all barcodes with leading zero issues}';
    
    protected $description = 'Check all products that have a specific barcode or find all duplicate barcodes (including leading zero issues)';

    public function handle()
    {
        if ($this->option('all')) {
            return $this->findAllDuplicates();
        }

        $barcode = $this->argument('barcode');
        
        if (!$barcode) {
            $this->error('Please provide a barcode or use --all option');
            return Command::FAILURE;
        }
        
        $this->info("🔍 Checking barcode: {$barcode}");
        $this->newLine();

        // Get all products with this barcode (numeric comparison to catch leading zeros)
        $products = Product::whereHas('barcodes', function ($query) use ($barcode) {
            $query->whereRaw('CAST(' . ProductBarcode::COL_BARCODE . ' AS CHAR) = ?', [$barcode])
                  ->orWhereRaw('CAST(' . ProductBarcode::COL_BARCODE . ' AS UNSIGNED) = CAST(? AS UNSIGNED)', [$barcode]);
        })->with(['barcodes', 'store'])->get();

        if ($products->isEmpty()) {
            $this->warn("❌ No products found with barcode: {$barcode}");
            return Command::SUCCESS;
        }

        $this->info("Found {$products->count()} product(s) with this barcode:");
        $this->newLine();

        foreach ($products as $index => $product) {
            $stock = $product->store->sum(StoreProducts::COL_STOCK);
            $this->line("" . ($index + 1) . ". Product ID: <fg=cyan>#{$product->id}</>");
            $this->line("   Name: <fg=white>{$product->name}</>");
            $this->line("   Total Stock: <fg=yellow>{$stock}</>");
            $this->line("   Reference: {$product->reference}");
            $this->line("   Supplier Code: {$product->supplier_code}");
            
            // Show all barcodes for this product
            $this->line("   Barcodes:");
            foreach ($product->barcodes as $bc) {
                $isPrimary = $bc->is_primary ? ' (Primary)' : '';
                $this->line("     • {$bc->barcode}{$isPrimary}");
            }
            
            // Show stock by store
            if ($product->store->isNotEmpty()) {
                $this->line("   Stock by Store:");
                foreach ($product->store as $storeProduct) {
                    $this->line("     • Store #{$storeProduct->store_id}: {$storeProduct->stock}");
                }
            }
            
            $this->newLine();
        }

        if ($products->count() > 1) {
            $this->warn("⚠️  DUPLICATE DETECTED! This barcode exists on {$products->count()} products.");
            $this->info("Run: php artisan products:merge-duplicates --barcode={$barcode} --dry-run");
        } else {
            $this->info("✅ No duplicates - only 1 product has this barcode");
        }

        return Command::SUCCESS;
    }

    /**
     * Find all duplicate barcodes including leading zero issues
     */
    private function findAllDuplicates()
    {
        $this->info("🔍 Searching for all duplicate barcodes (including leading zero issues)...");
        $this->newLine();

        // Find barcodes that are numerically the same but may have different string representations
        $duplicates = DB::select("
            SELECT 
                CAST(barcode AS UNSIGNED) as numeric_barcode,
                GROUP_CONCAT(DISTINCT barcode ORDER BY barcode) as string_barcodes,
                GROUP_CONCAT(DISTINCT product_id ORDER BY product_id) as product_ids,
                COUNT(DISTINCT product_id) as product_count,
                COUNT(DISTINCT barcode) as barcode_variations
            FROM product_barcodes
            GROUP BY CAST(barcode AS UNSIGNED)
            HAVING COUNT(DISTINCT product_id) > 1 OR COUNT(DISTINCT barcode) > 1
            ORDER BY product_count DESC, barcode_variations DESC
        ");

        if (empty($duplicates)) {
            $this->info("✅ No duplicate barcodes found!");
            return Command::SUCCESS;
        }

        $this->warn("Found " . count($duplicates) . " duplicate barcode(s)");
        $this->newLine();

        $index = 1;
        foreach ($duplicates as $duplicate) {
            $barcodeVariations = explode(',', $duplicate->string_barcodes);
            $productIds = explode(',', $duplicate->product_ids);
            
            // Check if it's a leading zero issue
            $hasLeadingZeroIssue = count($barcodeVariations) > 1;
            
            if ($hasLeadingZeroIssue) {
                $this->line("{$index}. <fg=red>⚠️  LEADING ZERO ISSUE</>");
                $this->line("   Numeric Value: <fg=yellow>{$duplicate->numeric_barcode}</>");
                $this->line("   String Variations: <fg=cyan>" . implode(', ', $barcodeVariations) . "</>");
            } else {
                $this->line("{$index}. <fg=cyan>Barcode: {$barcodeVariations[0]}</>");
            }
            
            $this->line("   Found on <fg=yellow>{$duplicate->product_count}</> product(s): " . implode(', ', array_map(fn($id) => "#$id", $productIds)));
            
            // Get detailed info for each product
            $products = Product::whereIn('id', $productIds)
                ->with(['barcodes', 'store'])
                ->get()
                ->sortByDesc(function ($product) {
                    return $product->store->sum(StoreProducts::COL_STOCK);
                });
            
            foreach ($products as $prodIndex => $product) {
                $stock = $product->store->sum(StoreProducts::COL_STOCK);
                $icon = $prodIndex === 0 ? '<fg=green>✓ KEEP</>' : '<fg=red>✗ REMOVE</>';
                
                // Find which barcode variation this product has
                $productBarcodes = $product->barcodes
                    ->whereIn('barcode', $barcodeVariations)
                    ->pluck('barcode')
                    ->toArray();
                
                $this->line("   {$icon} Product #{$product->id}: {$product->name}");
                $this->line("       Stock: {$stock} | Barcode(s): " . implode(', ', $productBarcodes));
            }
            
            $this->newLine();
            $index++;
        }

        $this->info("💡 To merge a specific barcode, use:");
        $this->line("   php artisan products:merge-duplicates --barcode=XXXXX --dry-run");
        $this->newLine();
        $this->info("💡 To merge all duplicates at once:");
        $this->line("   php artisan products:merge-duplicates --dry-run");

        return Command::SUCCESS;
    }
}
