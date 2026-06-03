<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliverySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'delivery_number',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Current delivery note sequence number',
                'store_id' => null,
            ],
            [
                'key' => 'prefix_delivery',
                'value' => 'BL-',
                'type' => 'string',
                'description' => 'Prefix for delivery note numbers',
                'store_id' => null,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key'], 'store_id' => $setting['store_id']],
                $setting
            );
        }

        $this->command->info('✅ Delivery settings created successfully!');
    }
}
