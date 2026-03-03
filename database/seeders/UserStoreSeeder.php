<?php

namespace Database\Seeders;

use App\Models\UserStore;
use Illuminate\Database\Seeder;

class UserStoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserStore::factory()->count(10)->create();
    }
}
