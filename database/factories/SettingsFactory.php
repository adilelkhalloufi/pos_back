<?php

namespace Database\Factories;

use App\Models\Settings;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Settings>
 */
class SettingsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $keys = [
            Settings::KEY_INVOICE_NUMBER,
            Settings::KEY_ORDER_SALE_NUMBER,
            Settings::KEY_ORDER_PURCHASE_NUMBER,
            Settings::KEY_PREFIX_INVOICE,
            Settings::KEY_PREFIX_ORDER,
            Settings::KEY_COMPANY_NAME,
            Settings::KEY_MAX_PRINT_COPIES,
        ];

        return [
            'key' => fake()->randomElement($keys),
            'value' => fake()->numberBetween(0, 100),
            'type' => 'integer',
            'description' => fake()->sentence(),
            'store_id' => Store::pluck('id')->random(),
        ];
    }
}
