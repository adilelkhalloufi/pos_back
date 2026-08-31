<?php

namespace Database\Seeders;

use App\Enums\EnumAccountStatue;
use App\Enums\ROLES;
use App\Models\Assurances;
use App\Models\Customer;
use App\Models\ModePayemnt;
use App\Models\Settings;
use App\Models\Store;
use App\Models\Suppliers;
use App\Models\TypeGlasses;
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
   

        $owner = User::factory()->create([
            'name' => 'adil',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'role' => ROLES::OWNER,
            'statue' => EnumAccountStatue::ACTIVE,
    
        ]);
        // Assign owner role from roles table
        $ownerRole = \App\Models\Role::where('name', 'owner')->first();
        if ($ownerRole) {
            $owner->assignRole($ownerRole);
        }

      

        $store1 = Store::create([
            'name' => 'adevoptique',
            'address' => 'address 1',
            'phone' => '123456789',

            'owner_id' => $owner->id,
        ]);
   

        $store3 = Store::create([
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

        $vender2 = User::factory()->create([
            'name' => 'vender2',
            'email' => 'vender2@vender2.com',
            'password' => Hash::make('password'),
            'role' => ROLES::VENDOR,
        ]);
        // Assign vendor role from roles table for store3
        if ($vendorRole) {
            $vender2->assignRole($vendorRole, $store3->id);
        }

        $userStore1 = UserStore::create([
            'user_id' => $vender1->id,
            'store_id' => $store1->id,
        ]);
        $userStore2 = UserStore::create([
            'user_id' => $vender2->id,
            'store_id' => $store3->id,
        ]);

    
        // ProductScenariosSeeder::run();

        $this->call(ProductScenariosSeeder::class);
     

        
    }
}
