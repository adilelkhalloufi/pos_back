<?php

namespace Database\Seeders;

use App\Enums\ROLES;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManagerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $manager = User::firstOrCreate(
            ['email' => 'manager@manager.com'],
            [
                'name' => 'manager',
                'password' => Hash::make('password'),
                'role' => ROLES::SUPER_ADMIN->value,
            ]
        );

        if ($manager->role !== ROLES::SUPER_ADMIN->value) {
            $manager->update(['role' => ROLES::SUPER_ADMIN->value]);
        }

        $managerRole = Role::where('name', ROLES::SUPER_ADMIN->value)->first();

        if ($managerRole) {
            $manager->assignRole($managerRole);
        }
    }
}
