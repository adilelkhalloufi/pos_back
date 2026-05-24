#!/usr/bin/env php
<?php

/**
 * Preview Excel File Before Import
 * 
 * This script reads the Excel file and shows a preview of what will be imported
 * without actually creating any database records.
 * 
 * Usage: php database/seeders/PreviewExcelImport.php
 */

require __DIR__ . '/../../vendor/autoload.php';

use OpenSpout\Reader\XLSX\Reader;

$excelFile = __DIR__ . '/../import/database.xlsx';

if (!file_exists($excelFile)) {
    echo "\n❌ Excel file not found: $excelFile\n";
    echo "📁 Please place the Excel file at: database/import/la_base_de_donnes_de_nouveau_systemes_18-05.xlsx\n\n";
    exit(1);
}

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║         Excel Import Preview - Product Data Analysis          ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n";
echo "\n";
echo "📄 File: " . basename($excelFile) . "\n";
echo "📏 Size: " . number_format(filesize($excelFile) / 1024, 2) . " KB\n";
echo "\n";

$reader = new Reader();
$reader->open($excelFile);

$totalProducts = 0;
$totalWithStock = 0;
$suppliers = [];
$categories = [];
$families = [];
$units = [];
$totalValue = 0;

$sheetIndex = 0;
foreach ($reader->getSheetIterator() as $sheet) {
    $sheetIndex++;
    $sheetName = $sheet->getName();

    echo "════════════════════════════════════════════════════════════════\n";
    echo "📊 Sheet #{$sheetIndex}: {$sheetName}\n";
    echo "════════════════════════════════════════════════════════════════\n\n";

    $rowIndex = 0;
    $headers = [];
    $sheetProducts = 0;

    foreach ($sheet->getRowIterator() as $row) {
        $rowIndex++;
        $cells = $row->getCells();
        $data = array_map(fn($cell) => $cell->getValue(), $cells);

        // First row is header
        if ($rowIndex === 1) {
            $headers = $data;
            echo "📋 Headers: " . implode(' | ', array_map(fn($h) => $h ?: 'N/A', $headers)) . "\n\n";

            // Show first 3 sample rows
            echo "📦 Sample Data (first 3 products):\n";
            echo str_repeat('-', 80) . "\n";
            continue;
        }

        // Skip empty rows
        if (empty(array_filter($data))) {
            continue;
        }

        // Map data to associative array
        $rowData = [];
        foreach ($headers as $index => $header) {
            $rowData[$header] = $data[$index] ?? null;
        }

        // Extract data
        $code = trim($rowData['CODE'] ?? '');
        $articleName = trim($rowData['ARTICLES'] ?? '');
        $supplierName = trim($rowData['FRS'] ?? 'N/A');
        $categoryName = trim($rowData['CATEGORIE'] ?? 'N/A');
        $familyName = trim($rowData['FAMILLE'] ?? 'N/A');
        $subFamilyName = trim($rowData['S FAMILLE'] ?? 'N/A');
        $unitName = trim($rowData['UNITEE'] ?? 'N/A');
        $unitPrice = (float) ($rowData['PRIX UN'] ?? 0);
        $tva = (float) ($rowData['TVA'] ?? 0);
        $quantity = (float) ($rowData['QUANTITE'] ?? 0);

        // Skip if no product name or code
        if (empty($articleName) || empty($code)) {
            continue;
        }

        $sheetProducts++;
        $totalProducts++;

        // Count unique values
        if (!empty($supplierName)) {
            $suppliers[$supplierName] = ($suppliers[$supplierName] ?? 0) + 1;
        }
        if (!empty($familyName)) {
            $families[$familyName] = ($families[$familyName] ?? 0) + 1;
        }
        if (!empty($categoryName)) {
            $categories[$categoryName] = ($categories[$categoryName] ?? 0) + 1;
        }
        if (!empty($unitName)) {
            $units[$unitName] = ($units[$unitName] ?? 0) + 1;
        }

        if ($quantity > 0) {
            $totalWithStock++;
            $totalValue += $quantity * $unitPrice;
        }

        // Show first 3 rows as sample
        if ($rowIndex <= 4) {
            $priceWithTax = $unitPrice * (1 + ($tva / 100));
            echo sprintf(
                "  %-15s | %-30s | %-12s | Qty: %6.2f | Price: %8.2f MAD\n",
                substr($code, 0, 15),
                substr($articleName, 0, 30),
                substr($unitName, 0, 12),
                $quantity,
                $priceWithTax
            );
        }
    }

    echo str_repeat('-', 80) . "\n";
    echo "✅ Products in this sheet: {$sheetProducts}\n\n";
}

$reader->close();

// Summary
echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                         IMPORT SUMMARY                         ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "📦 Total Products: {$totalProducts}\n";
echo "📊 Products with Stock: {$totalWithStock}\n";
echo "💰 Total Inventory Value: " . number_format($totalValue, 2) . " MAD\n\n";

echo "👥 Suppliers (" . count($suppliers) . "):\n";
arsort($suppliers);
$count = 0;
foreach (array_slice($suppliers, 0, 10) as $supplier => $productCount) {
    echo sprintf("  %-40s : %3d products\n", substr($supplier, 0, 40), $productCount);
    $count++;
}
if (count($suppliers) > 10) {
    echo "  ... and " . (count($suppliers) - 10) . " more suppliers\n";
}

echo "\n📂 Families/Categories (" . count($families) . "):\n";
arsort($families);
foreach (array_slice($families, 0, 10) as $family => $productCount) {
    echo sprintf("  %-40s : %3d products\n", substr($family, 0, 40), $productCount);
}
if (count($families) > 10) {
    echo "  ... and " . (count($families) - 10) . " more families\n";
}

echo "\n📏 Units (" . count($units) . "):\n";
arsort($units);
foreach ($units as $unit => $productCount) {
    echo sprintf("  %-20s : %3d products\n", substr($unit, 0, 20), $productCount);
}

echo "\n";
echo "════════════════════════════════════════════════════════════════\n";
echo "✅ Preview completed! The data looks good.\n";
echo "════════════════════════════════════════════════════════════════\n\n";

echo "To import this data, run:\n";
echo "  php artisan db:seed --class=ProductsFromExcelSeeder\n\n";
