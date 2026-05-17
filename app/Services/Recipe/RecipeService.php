<?php

namespace App\Services\Recipe;

use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\Product;
use App\Services\Costing\CostingService;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * RecipeService - Handles recipe management and cost calculations
 */
class RecipeService
{
    protected $costingService;

    public function __construct(CostingService $costingService)
    {
        $this->costingService = $costingService;
    }

    /**
     * Create a new recipe with ingredients
     * 
     * @param array $recipeData - Recipe details
     * @param array $ingredients - Array of ingredients with product_id, quantity, unit_id, etc.
     * @return Recipe
     * @throws Exception
     */
    public function createRecipe(array $recipeData, array $ingredients = []): Recipe
    {
        DB::beginTransaction();

        try {
            // Create the recipe
            $recipe = Recipe::create([
                'name' => $recipeData['name'],
                'description' => $recipeData['description'] ?? null,
                'instructions' => $recipeData['instructions'] ?? null,
                'yield_quantity' => $recipeData['yield_quantity'] ?? 1,
                'yield_unit_id' => $recipeData['yield_unit_id'] ?? null,
                'preparation_time_minutes' => $recipeData['preparation_time_minutes'] ?? null,
                'cooking_time_minutes' => $recipeData['cooking_time_minutes'] ?? null,
                'skill_level' => $recipeData['skill_level'] ?? null,
                'store_id' => $recipeData['store_id'],
                'user_id' => $recipeData['user_id'],
                'is_active' => $recipeData['is_active'] ?? true,
            ]);

            // Add ingredients if provided
            if (!empty($ingredients)) {
                foreach ($ingredients as $ingredient) {
                    $this->addIngredientToRecipe($recipe->id, $ingredient);
                }

                // Calculate total recipe cost
                $recipe->calculateTotalCost();
            }

            DB::commit();

            return $recipe->fresh(['ingredients.product', 'ingredients.unit', 'yieldUnit']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing recipe
     * 
     * @param int $recipeId
     * @param array $recipeData
     * @param array|null $ingredients - If provided, replaces all ingredients
     * @return Recipe
     * @throws Exception
     */
    public function updateRecipe(int $recipeId, array $recipeData, ?array $ingredients = null): Recipe
    {
        DB::beginTransaction();

        try {
            $recipe = Recipe::findOrFail($recipeId);

            // Update recipe details
            $recipe->update(array_filter([
                'name' => $recipeData['name'] ?? $recipe->name,
                'description' => $recipeData['description'] ?? $recipe->description,
                'instructions' => $recipeData['instructions'] ?? $recipe->instructions,
                'yield_quantity' => $recipeData['yield_quantity'] ?? $recipe->yield_quantity,
                'yield_unit_id' => $recipeData['yield_unit_id'] ?? $recipe->yield_unit_id,
                'preparation_time_minutes' => $recipeData['preparation_time_minutes'] ?? $recipe->preparation_time_minutes,
                'cooking_time_minutes' => $recipeData['cooking_time_minutes'] ?? $recipe->cooking_time_minutes,
                'skill_level' => $recipeData['skill_level'] ?? $recipe->skill_level,
                'is_active' => $recipeData['is_active'] ?? $recipe->is_active,
            ]));

            // If ingredients array is provided, replace all ingredients
            if ($ingredients !== null) {
                // Delete existing ingredients
                RecipeIngredient::where('recipe_id', $recipeId)->delete();

                // Add new ingredients
                foreach ($ingredients as $ingredient) {
                    $this->addIngredientToRecipe($recipeId, $ingredient);
                }
            }

            // Recalculate total cost
            $recipe->calculateTotalCost();

            DB::commit();

            return $recipe->fresh(['ingredients.product', 'ingredients.unit', 'yieldUnit']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Add an ingredient to a recipe
     * 
     * @param int $recipeId
     * @param array $ingredientData
     * @return RecipeIngredient
     */
    public function addIngredientToRecipe(int $recipeId, array $ingredientData): RecipeIngredient
    {
        $ingredient = RecipeIngredient::create([
            'recipe_id' => $recipeId,
            'product_id' => $ingredientData['product_id'],
            'quantity' => $ingredientData['quantity'],
            'unit_id' => $ingredientData['unit_id'],
            'waste_percentage' => $ingredientData['waste_percentage'] ?? 0,
            'preparation_note' => $ingredientData['preparation_note'] ?? null,
            'is_optional' => $ingredientData['is_optional'] ?? false,
        ]);

        // Calculate cost for this ingredient
        $ingredient->calculateCost();

        return $ingredient;
    }

    /**
     * Remove an ingredient from a recipe
     * 
     * @param int $recipeId
     * @param int $ingredientId
     * @return bool
     */
    public function removeIngredientFromRecipe(int $recipeId, int $ingredientId): bool
    {
        DB::beginTransaction();

        try {
            $ingredient = RecipeIngredient::where('recipe_id', $recipeId)
                ->where('id', $ingredientId)
                ->firstOrFail();

            $ingredient->delete();

            // Recalculate recipe cost
            $recipe = Recipe::find($recipeId);
            $recipe->calculateTotalCost();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an ingredient in a recipe
     * 
     * @param int $ingredientId
     * @param array $ingredientData
     * @return RecipeIngredient
     */
    public function updateIngredient(int $ingredientId, array $ingredientData): RecipeIngredient
    {
        DB::beginTransaction();

        try {
            $ingredient = RecipeIngredient::findOrFail($ingredientId);

            $ingredient->update(array_filter([
                'product_id' => $ingredientData['product_id'] ?? $ingredient->product_id,
                'quantity' => $ingredientData['quantity'] ?? $ingredient->quantity,
                'unit_id' => $ingredientData['unit_id'] ?? $ingredient->unit_id,
                'waste_percentage' => $ingredientData['waste_percentage'] ?? $ingredient->waste_percentage,
                'preparation_note' => $ingredientData['preparation_note'] ?? $ingredient->preparation_note,
                'is_optional' => $ingredientData['is_optional'] ?? $ingredient->is_optional,
            ]));

            // Recalculate ingredient cost
            $ingredient->calculateCost();

            // Recalculate recipe total cost
            $ingredient->recipe->calculateTotalCost();

            DB::commit();

            return $ingredient->fresh(['product', 'unit']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get recipe with all details including cost breakdown
     * 
     * @param int $recipeId
     * @return Recipe
     */
    public function getRecipeWithCostBreakdown(int $recipeId): Recipe
    {
        return Recipe::with([
            'ingredients.product',
            'ingredients.unit',
            'yieldUnit',
            'store',
            'user'
        ])->findOrFail($recipeId);
    }

    /**
     * Get all recipes for a store
     * 
     * @param int $storeId
     * @param bool $activeOnly
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getStoreRecipes(int $storeId, bool $activeOnly = true)
    {
        $query = Recipe::with(['ingredients.product', 'yieldUnit'])
            ->where('store_id', $storeId);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Recalculate costs for all recipes in a store
     * Useful after bulk price changes
     * 
     * @param int $storeId
     * @return int Number of recipes updated
     */
    public function recalculateAllRecipeCosts(int $storeId): int
    {
        $recipes = Recipe::where('store_id', $storeId)->get();
        $count = 0;

        foreach ($recipes as $recipe) {
            // Recalculate each ingredient cost first
            foreach ($recipe->ingredients as $ingredient) {
                $ingredient->calculateCost();
            }

            // Then recalculate recipe total
            $recipe->calculateTotalCost();
            $count++;
        }

        return $count;
    }

    /**
     * Calculate recipe profitability
     * 
     * @param int $recipeId
     * @param float $sellingPrice
     * @return array
     */
    public function calculateProfitability(int $recipeId, float $sellingPrice): array
    {
        $recipe = Recipe::findOrFail($recipeId);

        $cost = $recipe->cost_per_serving;
        $profit = $sellingPrice - $cost;
        $marginPercentage = $sellingPrice > 0 ? ($profit / $sellingPrice) * 100 : 0;

        return [
            'recipe_id' => $recipeId,
            'recipe_name' => $recipe->name,
            'cost_per_serving' => round($cost, 2),
            'selling_price' => round($sellingPrice, 2),
            'gross_profit' => round($profit, 2),
            'profit_margin_percentage' => round($marginPercentage, 2),
            'food_cost_percentage' => round(($cost / $sellingPrice) * 100, 2),
        ];
    }

    /**
     * Delete a recipe
     * 
     * @param int $recipeId
     * @return bool
     */
    public function deleteRecipe(int $recipeId): bool
    {
        DB::beginTransaction();

        try {
            $recipe = Recipe::findOrFail($recipeId);

            // Delete all ingredients first (cascade should handle this, but being explicit)
            RecipeIngredient::where('recipe_id', $recipeId)->delete();

            // Delete the recipe
            $recipe->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Clone a recipe (duplicate with new name)
     * 
     * @param int $recipeId
     * @param string $newName
     * @param int $userId
     * @return Recipe
     */
    public function cloneRecipe(int $recipeId, string $newName, int $userId): Recipe
    {
        DB::beginTransaction();

        try {
            $originalRecipe = Recipe::with('ingredients')->findOrFail($recipeId);

            // Create new recipe with same data
            $newRecipe = Recipe::create([
                'name' => $newName,
                'description' => $originalRecipe->description,
                'instructions' => $originalRecipe->instructions,
                'yield_quantity' => $originalRecipe->yield_quantity,
                'yield_unit_id' => $originalRecipe->yield_unit_id,
                'preparation_time_minutes' => $originalRecipe->preparation_time_minutes,
                'cooking_time_minutes' => $originalRecipe->cooking_time_minutes,
                'skill_level' => $originalRecipe->skill_level,
                'store_id' => $originalRecipe->store_id,
                'user_id' => $userId,
                'is_active' => true,
            ]);

            // Clone all ingredients
            foreach ($originalRecipe->ingredients as $ingredient) {
                RecipeIngredient::create([
                    'recipe_id' => $newRecipe->id,
                    'product_id' => $ingredient->product_id,
                    'quantity' => $ingredient->quantity,
                    'unit_id' => $ingredient->unit_id,
                    'waste_percentage' => $ingredient->waste_percentage,
                    'preparation_note' => $ingredient->preparation_note,
                    'is_optional' => $ingredient->is_optional,
                ]);
            }

            // Calculate costs for new recipe
            $newRecipe->calculateTotalCost();

            DB::commit();

            return $newRecipe->fresh(['ingredients.product', 'ingredients.unit']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
