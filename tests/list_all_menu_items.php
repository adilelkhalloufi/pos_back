<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Models\MenuItem;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "ALL MENU ITEMS (including inactive):\n";
echo str_repeat("=", 80) . "\n";

$items = MenuItem::with('product')->get();

foreach ($items as $item) {
    echo sprintf(
        "ID: %-3d | %-30s | Type: %-10s | Active: %-3s | Product ID: %s\n",
        $item->id,
        substr($item->name, 0, 30),
        $item->item_type,
        $item->is_active ? 'Yes' : 'No',
        $item->product_id ?? 'NULL'
    );
}

echo "\nTotal: " . $items->count() . " menu items\n";
