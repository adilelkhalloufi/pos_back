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
        return [

            Settings::COL_ORDER_PURCHASE_NUMBER => 0,
            Settings::COL_ORDER_SALE_NUMBER => 0,
            Settings::COL_STORE_ID => Store::pluck('id')->random(),
        ];
    }
}
