<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Product::class;

    public function definition(): array
    {

        // i want user has the store and the store has the product

        return [
            Product::COL_NAME => ucfirst(fake()->word()).' '.ucfirst(fake()->word()),
            Product::COL_DESCRIPTION => fake()->text(),
            Product::COL_PRICE => fake()->randomFloat(2, 0, 100),
            Product::COL_CODEBAR => fake()->countryCode(),
            Product::COL_IMAGE => fake()->imageUrl(),
            Product::COL_QUANTITY => fake()->randomNumber(2),
            Product::COL_ARCHIVE => $this->faker->boolean(),
            Product::COL_USER_ID => User::pluck('id')->random(),
            Product::COL_CATEGORY_ID => Category::pluck('id')->random(),
            Product::COL_STORE_ID => Store::pluck('id')->random(),

        ];
    }
}
