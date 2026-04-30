<?php

namespace App\Console\Commands;

use App\Enums\EnumAccountStatue;
use App\Enums\ROLES;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use App\Models\UserStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateStoreUser extends Command
{
    protected $signature = 'store:user:create
        {--name= : User full name}
        {--email= : Email address}
        {--password= : Password}
        {--phone= : Phone number}
        {--store= : Store ID}';

    protected $description = 'Create a vendor user and attach them to a store.';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->askRequired('User name');
        $email = $this->option('email') ?: $this->askRequired('User email');
        $password = $this->option('password') ?: $this->secret('Password (leave blank to use password)');
        $phone = $this->option('phone') ?: $this->ask('Phone (optional)');
        $storeId = $this->option('store') ?: $this->askRequired('Store ID');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.');
            return self::FAILURE;
        }

        $store = Store::find($storeId);

        if (! $store) {
            $this->error("Store ID {$storeId} not found.");
            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("A user with email {$email} already exists.");
            return self::FAILURE;
        }

        if (trim((string) $password) === '') {
            $password = 'password';
        }

        DB::beginTransaction();

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'phone' => $phone ?: null,
                'role' => ROLES::VENDOR->value,
                'statue' => EnumAccountStatue::ACTIVE->value,
            ]);

            $vendorRole = Role::firstOrCreate(
                ['name' => ROLES::VENDOR->value],
                ['display_name' => 'Vendor', 'description' => 'Store vendor']
            );

            $user->roles()->syncWithoutDetaching([
                $vendorRole->id => ['store_id' => $store->id],
            ]);

            UserStore::firstOrCreate([
                'user_id' => $user->id,
                'store_id' => $store->id,
            ]);

            DB::commit();
        } catch (\Throwable $exception) {
            DB::rollBack();
            $this->error('Failed to create vendor user: ' . $exception->getMessage());
            return self::FAILURE;
        }

        $this->info('Vendor user created successfully.');
        $this->info('ID: ' . $user->id);
        $this->info('Email: ' . $user->email);
        $this->info('Store ID: ' . $store->id);
        $this->info('Role: ' . ROLES::VENDOR->value);

        return self::SUCCESS;
    }

    protected function askRequired(string $question): string
    {
        $value = '';

        while (trim($value) === '') {
            $value = $this->ask($question);
        }

        return trim($value);
    }
}
