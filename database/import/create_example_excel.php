<?php

require __DIR__ . '/../../vendor/autoload.php';

use OpenSpout\Writer\XLSX\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;

$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "📝 Creating example Excel file for recipe import...\n\n";

$outputFile = __DIR__ . '/EXAMPLE - fich ingrediant.xlsx';

// Create writer
$writer = new Writer();
$writer->openToFile($outputFile);

// Create header style (bold)
$headerStyle = new Style();
$headerStyle->setFontBold();
$headerStyle->setBackgroundColor(Color::rgb(217, 225, 242));

// Write header row
$headers = ['Recette', 'Produit', 'Reference', 'Quantite', 'Unite', 'Perte %', 'Notes'];
$headerRow = Row::fromValues($headers, $headerStyle);
$writer->addRow($headerRow);

// Get some real products from database to use in examples
$products = \App\Models\Product::limit(20)->get(['name', 'reference'])->toArray();

echo "📦 Using products from your database:\n";
foreach (array_slice($products, 0, 5) as $p) {
    echo "  - {$p['name']} (Ref: {$p['reference']})\n";
}
echo "\n";

// Example data - using realistic recipes
$exampleData = [];

// Recipe 1: Margherita Pizza
if (isset($products[0])) {
    $exampleData[] = ['Pizza Margherita', $products[0]['name'], $products[0]['reference'], '0.5', 'kg', '0', 'Farine tamisée'];
}
if (isset($products[1])) {
    $exampleData[] = ['Pizza Margherita', $products[1]['name'], $products[1]['reference'], '0.3', 'kg', '10', 'Écraser les tomates'];
}
if (isset($products[2])) {
    $exampleData[] = ['Pizza Margherita', $products[2]['name'], $products[2]['reference'], '0.2', 'kg', '5', 'Râper le fromage'];
}
if (isset($products[3])) {
    $exampleData[] = ['Pizza Margherita', $products[3]['name'], $products[3]['reference'], '0.05', 'lt', '0', 'Huile d\'olive'];
}

// Recipe 2: Salade César
if (isset($products[4])) {
    $exampleData[] = ['Salade César', $products[4]['name'], $products[4]['reference'], '0.3', 'kg', '15', 'Laver et couper'];
}
if (isset($products[5])) {
    $exampleData[] = ['Salade César', $products[5]['name'], $products[5]['reference'], '0.15', 'kg', '5', 'Griller et couper'];
}
if (isset($products[6])) {
    $exampleData[] = ['Salade César', $products[6]['name'], $products[6]['reference'], '0.1', 'kg', '0', 'Parmesan râpé'];
}
if (isset($products[7])) {
    $exampleData[] = ['Salade César', $products[7]['name'], $products[7]['reference'], '0.08', 'lt', '0', 'Sauce César'];
}

// Recipe 3: Soupe de légumes
if (isset($products[8])) {
    $exampleData[] = ['Soupe de Légumes', $products[8]['name'], $products[8]['reference'], '0.2', 'kg', '20', 'Éplucher et couper'];
}
if (isset($products[9])) {
    $exampleData[] = ['Soupe de Légumes', $products[9]['name'], $products[9]['reference'], '0.15', 'kg', '15', 'Laver et couper'];
}
if (isset($products[10])) {
    $exampleData[] = ['Soupe de Légumes', $products[10]['name'], $products[10]['reference'], '0.1', 'kg', '10', 'Couper en dés'];
}
if (isset($products[11])) {
    $exampleData[] = ['Soupe de Légumes', $products[11]['name'], $products[11]['reference'], '1', 'lt', '0', 'Bouillon de légumes'];
}

// Recipe 4: Pâtes Carbonara
if (isset($products[12])) {
    $exampleData[] = ['Pâtes Carbonara', $products[12]['name'], $products[12]['reference'], '0.4', 'kg', '0', 'Pâtes sèches'];
}
if (isset($products[13])) {
    $exampleData[] = ['Pâtes Carbonara', $products[13]['name'], $products[13]['reference'], '0.15', 'kg', '5', 'Couper en lardons'];
}
if (isset($products[14])) {
    $exampleData[] = ['Pâtes Carbonara', $products[14]['name'], $products[14]['reference'], '4', 'un', '0', 'Jaunes d\'œufs'];
}
if (isset($products[15])) {
    $exampleData[] = ['Pâtes Carbonara', $products[15]['name'], $products[15]['reference'], '0.1', 'kg', '0', 'Parmesan râpé'];
}

// If we don't have enough products, add generic examples
if (count($exampleData) < 4) {
    $exampleData = [
        ['Pizza Margherita', 'Farine', '1001', '0.5', 'kg', '0', 'Farine tamisée'],
        ['Pizza Margherita', 'Tomates', '1002', '0.3', 'kg', '10', 'Écraser les tomates'],
        ['Pizza Margherita', 'Mozzarella', '1003', '0.2', 'kg', '5', 'Râper le fromage'],
        ['Pizza Margherita', 'Huile Olive', '1004', '0.05', 'lt', '0', 'Pour la pâte'],
        
        ['Salade César', 'Laitue', '2001', '0.3', 'kg', '15', 'Laver et couper'],
        ['Salade César', 'Poulet', '2002', '0.15', 'kg', '5', 'Griller et couper'],
        ['Salade César', 'Parmesan', '2003', '0.1', 'kg', '0', 'Râper finement'],
        ['Salade César', 'Sauce César', '2004', '0.08', 'lt', '0', 'Sauce maison'],
        
        ['Soupe de Légumes', 'Carottes', '3001', '0.2', 'kg', '20', 'Éplucher et couper'],
        ['Soupe de Légumes', 'Courgettes', '3002', '0.15', 'kg', '15', 'Laver et couper'],
        ['Soupe de Légumes', 'Pommes de terre', '3003', '0.1', 'kg', '10', 'Éplucher et couper'],
        ['Soupe de Légumes', 'Bouillon', '3004', '1', 'lt', '0', 'Bouillon de légumes'],
    ];
}

// Write data rows
foreach ($exampleData as $row) {
    $dataRow = Row::fromValues($row);
    $writer->addRow($dataRow);
}

$writer->close();

echo "✅ Example file created successfully!\n\n";

echo str_repeat('=', 80) . "\n";
echo "📄 FILE CREATED: EXAMPLE - fich ingrediant.xlsx\n";
echo "📍 LOCATION: database/import/EXAMPLE - fich ingrediant.xlsx\n";
echo str_repeat('=', 80) . "\n\n";

echo "📋 FILE STRUCTURE:\n";
echo "  Columns:\n";
foreach ($headers as $index => $header) {
    echo "    " . ($index + 1) . ". $header\n";
}
echo "\n";

echo "📊 EXAMPLE DATA:\n";
echo "  - " . count($exampleData) . " ingredient rows\n";
$recipeCount = count(array_unique(array_column($exampleData, 0)));
echo "  - $recipeCount recipes\n\n";

echo "💡 HOW TO USE:\n";
echo "  1. Open: database/import/EXAMPLE - fich ingrediant.xlsx\n";
echo "  2. Replace example data with your actual recipes\n";
echo "  3. Save as: fich ingrediant.xlsx\n";
echo "  4. Run: php artisan db:seed --class=RecipeIngredientsSeeder\n\n";

echo "📝 COLUMN DESCRIPTIONS:\n";
echo "  - Recette: Recipe name (same name for all ingredients of one recipe)\n";
echo "  - Produit: Product name from your database\n";
echo "  - Reference: Product reference code (found in products table)\n";
echo "  - Quantite: Quantity needed (number: 0.5, 1, 2.5, etc.)\n";
echo "  - Unite: Unit of measure (kg, g, lt, ml, un, bt, pq, paq)\n";
echo "  - Perte %: Waste percentage (0-100, optional)\n";
echo "  - Notes: Preparation notes (optional)\n\n";

echo "✨ Your available units: ut, lt, pq, un, kg, bt, paq\n";
echo "✨ Your database has: 730 products\n\n";

echo "🎉 Done! You can now open and edit the example file.\n";
