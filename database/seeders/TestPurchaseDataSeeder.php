<?php

namespace Database\Seeders;

use App\Enums\EnumOrderStatue;
use App\Models\Category;
use App\Models\ModePayemnt;
use App\Models\OrderPurchase;
use App\Models\OrderPurchaseItems;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\Store;
use App\Models\StoreProducts;
use App\Models\Suppliers;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestPurchaseDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first store and user for testing
        $store = Store::first();
        $user = User::first();

        if (!$store || !$user) {
            $this->command->error('No store or user found. Please run DatabaseSeeder first.');
            return;
        }



        // Get or create payment method
        $paymentMethod = ModePayemnt::first();


        $this->command->info('Creating 3 suppliers...');

        // Create 3 suppliers
        $suppliers = [];
        for ($i = 1; $i <= 3; $i++) {
            $suppliers[] = Suppliers::create([
                'first_name' => "Supplier",
                'last_name' => "Test $i",
                'company_name' => "Test Supplier Company $i",
                'email' => "supplier$i@test.com",
                'phone' => "060000000$i",
                'address' => "Address $i",
                'city' => "City $i",
                'country' => "Morocco",
                'zip_code' => "1000$i",
                'user_id' => $user->id,
                'store_id' => $store->id,
            ]);
        }

        $this->command->info('Creating 2 products with simple barcodes...');

        // Create 2 products with simple barcodes
        $product1 = Product::create([
            'name' => 'Test Product 1',
            'reference' => 'REF-001',
            'description' => 'Test product 1 for purchase orders',
            'price' => 100.00,
            'price_buy' => 80.00,
            'price_sell_1' => 100.00,
            'stock_alert' => 10,
            'is_active' => true,
            'is_stockable' => true,
            'archive' => false,
            'category_id' => Category::first()->id,
            'user_id' => $user->id,
            'store_id' => $store->id,
        ]);

        // Create barcode for product 1
        ProductBarcode::create([
            'product_id' => $product1->id,
            'barcode' => '123456',
            'is_primary' => true,
        ]);

        ProductBarcode::create([
            'product_id' => $product1->id,
            'barcode' => '654321',
            'is_primary' => false,
        ]);

        // Create store-product relationship for product 1
        StoreProducts::create([
            'store_id' => $store->id,
            'product_id' => $product1->id,
            'price' => $product1->price,
            'cost' => $product1->price_buy,
            'stock' => 0,
        ]);

        $product2 = Product::create([
            'name' => 'Test Product 2',
            'reference' => 'REF-002',
            'description' => 'Test product 2 for purchase orders',
            'price' => 150.00,
            'price_buy' => 120.00,
            'price_sell_1' => 150.00,
            'stock_alert' => 5,
            'is_active' => true,
            'is_stockable' => true,
            'archive' => false,
            'category_id' => Category::latest()->first()->id,
            'user_id' => $user->id,
            'store_id' => $store->id,
        ]);

        // Create barcode for product 2
        ProductBarcode::create([
            'product_id' => $product2->id,
            'barcode' => '789012',
            'is_primary' => true,
        ]);
        ProductBarcode::create([
            'product_id' => $product2->id,
            'barcode' => '741852',
            'is_primary' => false,
        ]);

        // Create store-product relationship for product 2
        StoreProducts::create([
            'store_id' => $store->id,
            'product_id' => $product2->id,
            'price' => $product2->price,
            'cost' => $product2->price_buy,
            'stock' => 0,
        ]);

        $this->command->info('Creating 2 unvalidated purchase orders...');

        // Create first purchase order (PENDING - not validated)
        $purchase1 = OrderPurchase::create([
            'order_number' => 'Brouillon',
            'reference' => 'TEST-PO-001',
            'status' => EnumOrderStatue::PENDING->value,
            'supplier_id' => $suppliers[0]->id,
            'paid_method_id' => $paymentMethod->id,
            'user_id' => $user->id,
            'store_id' => $store->id,
            'public_note' => 'Test purchase order 1 - Not validated yet',
            'private_note' => 'This is a test order for validation',
        ]);

        // Add items to first purchase order
        OrderPurchaseItems::create([
            'order_id' => $purchase1->id,
            'product_id' => $product1->id,
            'store_id' => $store->id,
            'name' => $product1->name,
            'quantity' => 10,
            'price' => $product1->price_buy,
            'total' => 10 * $product1->price_buy,
        ]);

        OrderPurchaseItems::create([
            'order_id' => $purchase1->id,
            'product_id' => $product2->id,
            'store_id' => $store->id,
            'name' => $product2->name,
            'quantity' => 5,
            'price' => $product2->price_buy,
            'total' => 5 * $product2->price_buy,
        ]);

        // Create second purchase order (PENDING - not validated)
        $purchase2 = OrderPurchase::create([
            'order_number' => 'Brouillon',
            'reference' => 'TEST-PO-002',
            'status' => EnumOrderStatue::PENDING->value,
            'supplier_id' => $suppliers[1]->id,
            'paid_method_id' => $paymentMethod->id,
            'user_id' => $user->id,
            'store_id' => $store->id,
            'public_note' => 'Test purchase order 2 - Not validated yet',
            'private_note' => 'Second test order for validation',
        ]);

        // Add items to second purchase order
        OrderPurchaseItems::create([
            'order_id' => $purchase2->id,
            'product_id' => $product1->id,
            'store_id' => $store->id,
            'name' => $product1->name,
            'quantity' => 20,
            'price' => $product1->price_buy,
            'total' => 20 * $product1->price_buy,
        ]);

        OrderPurchaseItems::create([
            'order_id' => $purchase2->id,
            'product_id' => $product2->id,
            'store_id' => $store->id,
            'name' => $product2->name,
            'quantity' => 15,
            'price' => $product2->price_buy,
            'total' => 15 * $product2->price_buy,
        ]);

        $this->command->info('✅ Test data created successfully!');
        $this->command->info('-----------------------------------');
        $this->command->info('Created 3 suppliers');
        $this->command->info('Created 2 products with barcodes: 123456, 789012');
        $this->command->info('Created 2 store-pr');
        $this->command->info('Created 2 product barcodes (123456, 789012) in product_barcodes tableal stock 0');
        $this->command->info('Created 2 unvalidated purchase orders (status: PENDING)');
        $this->command->info('-----------------------------------');
        $this->command->info('Product 1: ' . $product1->name . ' (Barcode: 123456)');
        $this->command->info('Product 2: ' . $product2->name . ' (Barcode: 789012)');
        $this->command->info('Store: ' . $store->name . ' (ID: ' . $store->id . ')');
        $this->command->info('Purchase Order 1 ID: ' . $purchase1->id . ' (Supplier: ' . $suppliers[0]->company_name . ')');
        $this->command->info('Purchase Order 2 ID: ' . $purchase2->id . ' (Supplier: ' . $suppliers[1]->company_name . ')');
    }
}
