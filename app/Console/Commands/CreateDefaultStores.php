<?php

namespace App\Console\Commands;

use App\Enums\ROLES;
use App\Models\Store;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateDefaultStores extends Command
{
    protected $signature = 'stores:create-defaults
        {--owner-id= : Owner user ID}
        {--owner-email= : Owner email}
        {--store1-name= : Replace the first store name}
        {--store2-name= : Replace the second store name}
        {--phone= : Phone number for created stores}
        {--address= : Address for created stores}';

    protected $description = 'Create a standard set of stores for a selected owner.';

    public function handle(): int
    {
        $ownerId = $this->option('owner-id');
        $ownerEmail = $this->option('owner-email');

        $owner = null;

        if ($ownerId) {
            $owner = User::find($ownerId);
        }

        if (! $owner && $ownerEmail) {
            $owner = User::where('email', $ownerEmail)->first();
        }

        if (! $owner) {
            $owner = User::where('role', ROLES::OWNER->value)->first();
        }

        if (! $owner) {
            $this->error('Owner user not found. Use --owner-id or --owner-email.');
            return self::FAILURE;
        }

        $defaultNames = [
            'Economat',
            'Bar',
            'Chiringuito',
            'Restaurant',
            'Cuisine',
            'Matériels',
            'Stewarding',
        ];

        $store1Name = $this->option('store1-name');
        $store2Name = $this->option('store2-name');

        if ($store1Name) {
            $defaultNames[0] = $store1Name;
        }

        if ($store2Name) {
            $defaultNames[1] = $store2Name;
        }

        $phone = $this->option('phone') ?: null;
        $address = $this->option('address') ?: null;

        DB::beginTransaction();

        try {
            foreach ($defaultNames as $name) {
                $store = Store::firstOrCreate(
                    [
                        Store::COL_NAME => $name,
                        Store::COL_OWNER_ID => $owner->id,
                    ],
                    [
                        Store::COL_EMAIL => null,
                        Store::COL_PHONE => $phone,
                        Store::COL_ADDRESS => $address,
                    ]
                );

                if ($store->wasRecentlyCreated) {
                    $this->info("Created store: {$store->name} (ID: {$store->id})");
                } else {
                    $this->comment("Store already exists: {$store->name} (ID: {$store->id})");
                }
            }

            DB::commit();
            $this->info('Default stores created or verified successfully.');
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            DB::rollBack();
            $this->error('Failed to create default stores: ' . $exception->getMessage());
            return self::FAILURE;
        }
    }
}
