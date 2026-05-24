<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Store;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $store = Store::query()->first();
        $user = User::query()->first();

        if (!$store || !$user) {
            $this->command->error('Store or User not found. Please run StoreSeeds and UserStoreSeeder first.');
            return;
        }

        // Get units for recipes
        $kgUnit = Unit::query()->where('symbol', 'kg')->first();
        $gUnit = Unit::query()->where('symbol', 'g')->first();
        $mlUnit = Unit::query()->where('symbol', 'ml')->first();
        $lUnit = Unit::query()->where('symbol', 'L')->first();
        $pcUnit = Unit::query()->where('symbol', 'pc')->first();

        // Get products to use as ingredients
        $chickenBreast = Product::query()->where('reference', 'REF-001')->first();
        $rice = Product::query()->where('reference', 'REF-002')->first();
        $lettuce = Product::query()->where('reference', 'REF-003')->first();
        $tomatoes = Product::query()->where('refeapirence', 'REF-004')->first();
        $oliveOil = Product::query()->where('reference', 'REF-005')->first();

        $recipes = [
            [
                'name' => 'Grilled Chicken with Rice',
                'description' => 'Tender grilled chicken breast served with fluffy white rice',
                'instructions' => "1. Season chicken breast with salt and pepper\n2. Grill chicken for 6-8 minutes per side\n3. Cook rice according to package instructions\n4. Serve chicken over rice\n5. Garnish with fresh herbs",
                'yield_quantity' => 2,
                'yield_unit_id' => $pcUnit?->id,
                'preparation_time_minutes' => 15,
                'cooking_time_minutes' => 30,
                'skill_level' => 'easy',
                'ingredients' => [
                    [
                        'product' => $chickenBreast,
                        'quantity' => 0.4,
                        'unit' => $kgUnit,
                        'waste_percentage' => 5.00,
                        'preparation_note' => 'Trim excess fat',
                    ],
                    [
                        'product' => $rice,
                        'quantity' => 0.2,
                        'unit' => $kgUnit,
                        'waste_percentage' => 0,
                        'preparation_note' => 'Rinse thoroughly before cooking',
                    ],
                    [
                        'product' => $oliveOil,
                        'quantity' => 0.02,
                        'unit' => $lUnit,
                        'waste_percentage' => 0,
                        'preparation_note' => 'For grilling',
                    ],
                ],
            ],
            [
                'name' => 'Fresh Garden Salad',
                'description' => 'Crisp lettuce and ripe tomatoes with olive oil dressing',
                'instructions' => "1. Wash and chop lettuce into bite-sized pieces\n2. Dice tomatoes\n3. Combine in a large bowl\n4. Drizzle with olive oil\n5. Toss gently and season to taste",
                'yield_quantity' => 4,
                'yield_unit_id' => $pcUnit?->id,
                'preparation_time_minutes' => 10,
                'cooking_time_minutes' => 0,
                'skill_level' => 'easy',
                'ingredients' => [
                    [
                        'product' => $lettuce,
                        'quantity' => 0.3,
                        'unit' => $kgUnit,
                        'waste_percentage' => 15.00,
                        'preparation_note' => 'Remove outer leaves, wash and chop',
                    ],
                    [
                        'product' => $tomatoes,
                        'quantity' => 0.4,
                        'unit' => $kgUnit,
                        'waste_percentage' => 10.00,
                        'preparation_note' => 'Dice into small cubes',
                    ],
                    [
                        'product' => $oliveOil,
                        'quantity' => 0.05,
                        'unit' => $lUnit,
                        'waste_percentage' => 0,
                        'preparation_note' => 'For dressing',
                    ],
                ],
            ],
            [
                'name' => 'Chicken Salad Bowl',
                'description' => 'Healthy bowl with grilled chicken, rice, and fresh vegetables',
                'instructions' => "1. Grill chicken breast until cooked through\n2. Cook rice separately\n3. Prepare fresh salad with lettuce and tomatoes\n4. Slice grilled chicken\n5. Arrange rice, salad, and chicken in bowl\n6. Drizzle with olive oil",
                'yield_quantity' => 1,
                'yield_unit_id' => $pcUnit?->id,
                'preparation_time_minutes' => 20,
                'cooking_time_minutes' => 25,
                'skill_level' => 'medium',
                'ingredients' => [
                    [
                        'product' => $chickenBreast,
                        'quantity' => 0.2,
                        'unit' => $kgUnit,
                        'waste_percentage' => 5.00,
                        'preparation_note' => 'Grill and slice',
                    ],
                    [
                        'product' => $rice,
                        'quantity' => 0.1,
                        'unit' => $kgUnit,
                        'waste_percentage' => 0,
                        'preparation_note' => 'Cook until fluffy',
                    ],
                    [
                        'product' => $lettuce,
                        'quantity' => 0.1,
                        'unit' => $kgUnit,
                        'waste_percentage' => 15.00,
                        'preparation_note' => 'Chop fresh',
                    ],
                    [
                        'product' => $tomatoes,
                        'quantity' => 0.15,
                        'unit' => $kgUnit,
                        'waste_percentage' => 10.00,
                        'preparation_note' => 'Dice into cubes',
                    ],
                    [
                        'product' => $oliveOil,
                        'quantity' => 0.015,
                        'unit' => $lUnit,
                        'waste_percentage' => 0,
                        'preparation_note' => 'Drizzle on top',
                    ],
                ],
            ],
            [
                'name' => 'Simple Rice Bowl',
                'description' => 'Steamed rice with vegetables and olive oil',
                'instructions' => "1. Cook rice until tender\n2. Sauté diced tomatoes lightly\n3. Mix rice with tomatoes\n4. Top with fresh lettuce\n5. Drizzle with olive oil",
                'yield_quantity' => 2,
                'yield_unit_id' => $pcUnit?->id,
                'preparation_time_minutes' => 10,
                'cooking_time_minutes' => 20,
                'skill_level' => 'easy',
                'ingredients' => [
                    [
                        'product' => $rice,
                        'quantity' => 0.25,
                        'unit' => $kgUnit,
                        'waste_percentage' => 0,
                        'preparation_note' => 'Steam until perfect',
                    ],
                    [
                        'product' => $tomatoes,
                        'quantity' => 0.2,
                        'unit' => $kgUnit,
                        'waste_percentage' => 10.00,
                        'preparation_note' => 'Dice and sauté briefly',
                    ],
                    [
                        'product' => $lettuce,
                        'quantity' => 0.1,
                        'unit' => $kgUnit,
                        'waste_percentage' => 15.00,
                        'preparation_note' => 'Shred finely',
                    ],
                    [
                        'product' => $oliveOil,
                        'quantity' => 0.03,
                        'unit' => $lUnit,
                        'waste_percentage' => 0,
                        'preparation_note' => 'For finishing',
                    ],
                ],
            ],
            [
                'name' => 'Herb-Roasted Chicken',
                'description' => 'Juicy roasted chicken breast with herbs',
                'instructions' => "1. Marinate chicken with olive oil and herbs\n2. Roast in oven at 180°C for 25-30 minutes\n3. Let rest for 5 minutes before serving\n4. Slice and serve",
                'yield_quantity' => 3,
                'yield_unit_id' => $pcUnit?->id,
                'preparation_time_minutes' => 15,
                'cooking_time_minutes' => 35,
                'skill_level' => 'medium',
                'ingredients' => [
                    [
                        'product' => $chickenBreast,
                        'quantity' => 0.6,
                        'unit' => $kgUnit,
                        'waste_percentage' => 5.00,
                        'preparation_note' => 'Marinate for 30 minutes if possible',
                    ],
                    [
                        'product' => $oliveOil,
                        'quantity' => 0.03,
                        'unit' => $lUnit,
                        'waste_percentage' => 0,
                        'preparation_note' => 'For marinating',
                    ],
                ],
            ],
        ];

        foreach ($recipes as $recipeData) {
            // Create recipe
            $recipe = Recipe::create([
                'name' => $recipeData['name'],
                'description' => $recipeData['description'],
                'instructions' => $recipeData['instructions'],
                'yield_quantity' => $recipeData['yield_quantity'],
                'yield_unit_id' => $recipeData['yield_unit_id'],
                'preparation_time_minutes' => $recipeData['preparation_time_minutes'],
                'cooking_time_minutes' => $recipeData['cooking_time_minutes'],
                'skill_level' => $recipeData['skill_level'],
                'total_cost' => 0, // Will be calculated
                'cost_per_serving' => 0, // Will be calculated
                'version' => 1,
                'is_active' => true,
                'store_id' => $store->id,
                'user_id' => $user->id,
            ]);

            $totalCost = 0;

            // Create recipe ingredients
            foreach ($recipeData['ingredients'] as $ingredientData) {
                if (!$ingredientData['product'] || !$ingredientData['unit']) {
                    continue;
                }

                // Calculate ingredient cost
                $productCost = $ingredientData['product']->price_buy ?? 0;
                $ingredientCost = $productCost * $ingredientData['quantity'];
                $totalCost += $ingredientCost;

                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'product_id' => $ingredientData['product']->id,
                    'quantity' => $ingredientData['quantity'],
                    'unit_id' => $ingredientData['unit']->id,
                    'waste_percentage' => $ingredientData['waste_percentage'],
                    'preparation_note' => $ingredientData['preparation_note'],
                    'is_optional' => false,
                    'cost' => $ingredientCost,
                ]);
            }

            // Update recipe with calculated costs
            $costPerServing = $recipeData['yield_quantity'] > 0
                ? $totalCost / $recipeData['yield_quantity']
                : $totalCost;

            $recipe->update([
                'total_cost' => $totalCost,
                'cost_per_serving' => $costPerServing,
            ]);

            $this->command->info("Created recipe: {$recipe->name} (Cost: {$totalCost}, Per serving: {$costPerServing})");
        }

        $this->command->info('Recipe seeding completed successfully!');
    }
}
