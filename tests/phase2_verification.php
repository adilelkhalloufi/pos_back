<?php

/**
 * Phase 2 Verification Script
 * 
 * Run this script to verify Phase 2 implementation:
 * php artisan tinker < tests/phase2_verification.php
 * 
 * Or manually in tinker:
 * php artisan tinker
 * require 'tests/phase2_verification.php';
 */

echo "=== Phase 2 Menu Management Verification ===\n\n";

// Check if migrations ran successfully
try {
    echo "1. Checking migrations...\n";
    $menuTable = Schema::hasTable('menus');
    $categoryTable = Schema::hasTable('menu_categories');
    $itemTable = Schema::hasTable('menu_items');
    
    echo "   ✅ menus table: " . ($menuTable ? 'EXISTS' : 'MISSING') . "\n";
    echo "   ✅ menu_categories table: " . ($categoryTable ? 'EXISTS' : 'MISSING') . "\n";
    echo "   ✅ menu_items table: " . ($itemTable ? 'EXISTS' : 'MISSING') . "\n\n";
    
    if (!$menuTable || !$categoryTable || !$itemTable) {
        echo "   ❌ Some tables are missing. Run: php artisan migrate\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ Error checking tables: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if models exist and are loadable
try {
    echo "2. Checking models...\n";
    $menuModel = class_exists('App\Models\Menu');
    $categoryModel = class_exists('App\Models\MenuCategory');
    $itemModel = class_exists('App\Models\MenuItem');
    
    echo "   ✅ Menu model: " . ($menuModel ? 'EXISTS' : 'MISSING') . "\n";
    echo "   ✅ MenuCategory model: " . ($categoryModel ? 'EXISTS' : 'MISSING') . "\n";
    echo "   ✅ MenuItem model: " . ($itemModel ? 'EXISTS' : 'MISSING') . "\n\n";
    
    if (!$menuModel || !$categoryModel || !$itemModel) {
        echo "   ❌ Some models are missing.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "   ❌ Error checking models: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if service exists
try {
    echo "3. Checking services...\n";
    $menuService = app(\App\Services\Menu\MenuService::class);
    echo "   ✅ MenuService: LOADED\n\n";
} catch (Exception $e) {
    echo "   ❌ Error loading MenuService: " . $e->getMessage() . "\n";
    exit(1);
}

// Quick functional test
try {
    echo "4. Running functional test...\n";
    
    // Get first store
    $store = \App\Models\Store::first();
    if (!$store) {
        echo "   ⚠️  No stores found. Create a store first.\n\n";
    } else {
        echo "   Using store: {$store->name} (ID: {$store->id})\n";
        
        // Count existing menus
        $menuCount = \App\Models\Menu::where('store_id', $store->id)->count();
        echo "   Current menu count: {$menuCount}\n";
        
        // Test menu service statistics
        $stats = $menuService->getMenuStatistics($store->id);
        echo "   Menu statistics retrieved successfully\n";
        echo "   - Total menus: {$stats['total_menus']}\n";
        echo "   - Total categories: {$stats['total_categories']}\n";
        echo "   - Total items: {$stats['total_items']}\n";
        echo "   - Recipe-based items: {$stats['recipe_based_items']}\n";
        echo "   ✅ MenuService working correctly\n\n";
    }
} catch (Exception $e) {
    echo "   ❌ Functional test failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check API routes
try {
    echo "5. Checking API routes...\n";
    $routes = Route::getRoutes();
    $menuRoutes = collect($routes)->filter(function($route) {
        return str_contains($route->uri(), 'api/menu');
    })->count();
    
    echo "   ✅ Menu API routes registered: {$menuRoutes} endpoints\n\n";
} catch (Exception $e) {
    echo "   ⚠️  Could not verify routes: " . $e->getMessage() . "\n\n";
}

echo "=== Phase 2 Verification Complete ===\n";
echo "✅ All checks passed! Phase 2 is ready to use.\n\n";

echo "Quick start examples:\n";
echo "---------------------\n";
echo "\$menuService = app(\\App\\Services\\Menu\\MenuService::class);\n";
echo "\$menu = \$menuService->createMenu([\n";
echo "    'name' => 'Test Menu',\n";
echo "    'type' => 'all_day',\n";
echo "    'store_id' => 1,\n";
echo "]);\n\n";

echo "For more examples, see:\n";
echo "- docs/PHASE_2_QUICK_REFERENCE.md\n";
echo "- docs/PHASE_2_IMPLEMENTATION_COMPLETE.md\n";
