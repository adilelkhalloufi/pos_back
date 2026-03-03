<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerRole = Role::firstOrCreate(
            ['name' => 'owner'],
            [
                'display_name' => 'Owner',
                'description' => 'Full system access ',
                'is_system' => true,
            ]
        );


        // Create Manager Role
        $managerRole = Role::firstOrCreate(
            ['name' => 'manager'],
            [
                'display_name' => 'Manager',
                'description' => 'Store manager ',
                'is_system' => true,
            ]
        );


        // Create Vendor/Cashier Role
        $vendorRole = Role::firstOrCreate(
            ['name' => 'vendor'],
            [
                'display_name' => 'Vendor/Cashier',
                'description' => 'Sales and customer management',
                'is_system' => true,
            ]
        );




        // Create Inventory Manager Role
        $inventoryRole = Role::firstOrCreate(
            ['name' => 'inventory_manager'],
            [
                'display_name' => 'Inventory Manager',
                'description' => 'Manages products, stock, and suppliers',
                'is_system' => true,
            ]
        );


        // Create Accountant Role
        $accountantRole = Role::firstOrCreate(
            ['name' => 'accountant'],
            [
                'display_name' => 'Accountant',
                'description' => 'Manages payments, invoices, and reports',
                'is_system' => true,
            ]
        );

        // Create Read-Only Role
        $viewerRole = Role::firstOrCreate(
            ['name' => 'viewer'],
            [
                'display_name' => 'Viewer',
                'description' => 'Read-only access to data',
                'is_system' => false,
            ]
        );


        $this->command->info('Roles created  assigned successfully!');
    }
}
