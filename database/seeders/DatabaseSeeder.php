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
        
        $owner = User::create([
            'name' => 'adil',
            'email' => 'test@test.com',
            'password' => Hash::make('password'),
            'role' => ROLES::OWNER,
            'statue' => EnumAccountStatue::ACTIVE,
            'plan_id' => 2,
        ]);
      
        $store1 = Store::create([
            'name' => 'DutyFree',
            'address' => 'address 1',
            'phone' => '123456789',
            'owner_id' => $owner->id,
        ]);
      
        $vender1 = User::create([
            'name' => 'vender1',
            'email' => 'vender1@vender1.com',
            'password' => Hash::make('password'),
            'role' => ROLES::VENDOR,
        ]);
     
        $userStore1 = UserStore::create([
            'user_id' => $vender1->id,
            'store_id' => $store1->id,
        ]);
       
        $settings1 = Settings::create([
            Settings::COL_ORDER_SALE_NUMBER => 1,
            Settings::COL_ORDER_PURCHASE_NUMBER => 1,
            Settings::COL_INVOICE_NUMBER => 1,
            Settings::COL_STORE_ID => $store1->id,
        ]);
    
        $this->call(ModePayemntSeeder::class);
        // $this->call(SupplierSeeder::class);
        $this->call(CategorySeeds::class);
       
    }
}
