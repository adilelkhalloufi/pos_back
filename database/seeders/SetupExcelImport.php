#!/usr/bin/env php
<?php

/**
 * Excel Import Setup Helper
 * 
 * This script helps you set up and verify the Excel import process.
 * 
 * Usage: php database/seeders/SetupExcelImport.php
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║           Excel Import Setup Helper for Products             ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$baseDir = __DIR__ . '/../..';
$importDir = __DIR__ . '/../import';
$excelFile = $importDir . '/database.xlsx';

echo "🔍 Checking setup...\n\n";

// Check 1: Import directory exists
echo "1️⃣  Checking import directory...\n";
if (!is_dir($importDir)) {
    echo "   ❌ Import directory not found. Creating it...\n";
    mkdir($importDir, 0755, true);
    echo "   ✅ Created: database/import/\n";
} else {
    echo "   ✅ Import directory exists\n";
}

// Check 2: Excel file exists
echo "\n2️⃣  Checking Excel file...\n";
if (!file_exists($excelFile)) {
    echo "   ❌ Excel file not found!\n";
    echo "\n";
    echo "   📋 Please place your Excel file at:\n";
    echo "      database/import/la_base_de_donnes_de_nouveau_systemes_18-05.xlsx\n";
    echo "\n";
    echo "   The Excel file should have these columns:\n";
    echo "   - CODE: Product code/reference\n";
    echo "   - ARTICLES: Product name\n";
    echo "   - FRS: Supplier name\n";
    echo "   - CATEGORIE: Category\n";
    echo "   - FAMILLE: Family\n";
    echo "   - S FAMILLE: Sub-family\n";
    echo "   - UNITEE: Unit of measure\n";
    echo "   - PRIX UN: Unit price\n";
    echo "   - TVA: Tax percentage\n";
    echo "   - QUANTITE: Quantity\n";
    echo "\n";
} else {
    echo "   ✅ Excel file found\n";
    echo "      📏 Size: " . number_format(filesize($excelFile) / 1024, 2) . " KB\n";
}

// Check 3: Vendor dependencies
echo "\n3️⃣  Checking OpenSpout library...\n";
$composerLock = $baseDir . '/composer.lock';
if (file_exists($composerLock)) {
    $content = file_get_contents($composerLock);
    if (strpos($content, 'openspout/openspout') !== false) {
        echo "   ✅ OpenSpout library installed\n";
    } else {
        echo "   ❌ OpenSpout not found. Installing...\n";
        echo "   Run: composer require openspout/openspout\n";
    }
} else {
    echo "   ⚠️  Cannot verify dependencies\n";
}

// Check 4: Database connection
echo "\n4️⃣  Checking database connection...\n";
$envFile = $baseDir . '/.env';
if (file_exists($envFile)) {
    echo "   ✅ .env file exists\n";

    // Try to load env and check DB settings
    $envContent = file_get_contents($envFile);
    if (preg_match('/DB_CONNECTION=(.+)/', $envContent, $matches)) {
        echo "   📊 Database: " . trim($matches[1]) . "\n";
    }
} else {
    echo "   ⚠️  .env file not found\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";

if (file_exists($excelFile)) {
    echo "✅ Setup looks good!\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";
    echo "📋 Next steps:\n\n";
    echo "1️⃣  Preview the data (recommended):\n";
    echo "   php database/seeders/PreviewExcelImport.php\n\n";
    echo "2️⃣  Run the import:\n";
    echo "   php artisan db:seed --class=ProductsFromExcelSeeder\n\n";
    echo "3️⃣  Or add to DatabaseSeeder.php and run:\n";
    echo "   php artisan db:seed\n\n";
} else {
    echo "⚠️  Setup incomplete!\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";
    echo "📋 To complete setup:\n\n";
    echo "1️⃣  Place your Excel file at:\n";
    echo "   database/import/la_base_de_donnes_de_nouveau_systemes_18-05.xlsx\n\n";
    echo "2️⃣  Run this script again to verify:\n";
    echo "   php database/seeders/SetupExcelImport.php\n\n";
}

echo "\n";
