<?php

namespace Database\Seeders;

use App\Enums\EnumAccountStatue;
use App\Enums\ROLES;
use App\Models\ModePayemnt;
use App\Models\Settings;
use App\Models\Store;
use App\Models\User;
use App\Models\UserStore;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create plans first
        $this->call(PlansSeeder::class);

        $this->call(RoleSeeder::class);
        $this->call(ManagerUserSeeder::class);

 
        $owner = User::factory()->create([
            'name' => 'adil',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'role' => ROLES::OWNER,
            'statue' => EnumAccountStatue::ACTIVE,
            'plan_id' => 2,
        ]);
        // Assign owner role from roles table
        $ownerRole = \App\Models\Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $owner->assignRole($ownerRole);
        }

      
    

        $store1 = Store::factory()->create([
            'name' => 'adevoptique',
            'address' => 'address 1',
            'phone' => '123456789',

            'owner_id' => $owner->id,
        ]);
      

        $store3 = Store::factory()->create([
            'name' => 'adevoptique 2',
            'address' => 'address 2',
            'phone' => '123456789',

            'owner_id' => $owner->id,
        ]);
        $vender1 = User::factory()->create([
            'name' => 'vender1',
            'email' => 'vender1@vender1.com',
            'password' => Hash::make('password'),
            'role' => ROLES::VENDOR,
        ]);
        // Assign vendor role from roles table for store1
        $vendorRole = \App\Models\Role::where('name', 'vendor')->first();
        if ($vendorRole) {
            $vender1->assignRole($vendorRole, $store1->id);
        }

   

        $userStore1 = UserStore::factory()->create([
            'user_id' => $vender1->id,
            'store_id' => $store1->id,
        ]);
       


        $settings1 = Settings::factory()->create([
            Settings::COL_ORDER_SALE_NUMBER => 1,
            Settings::COL_ORDER_PURCHASE_NUMBER => 1,
            Settings::COL_INVOICE_NUMBER => 1,
            Settings::COL_STORE_ID => $store1->id,
        ]);
    

        $settings3 = Settings::factory()->create([
            Settings::COL_ORDER_SALE_NUMBER => 1,
            Settings::COL_ORDER_PURCHASE_NUMBER => 1,
            Settings::COL_INVOICE_NUMBER => 1,
            Settings::COL_STORE_ID => $store3->id,
        ]);



         $this->call(ModePayemntSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(CategorySeeds::class);
         // $this->call(CustomerSeeder::class);
        // $this->call(StoreSeeds::class);
    }
}
