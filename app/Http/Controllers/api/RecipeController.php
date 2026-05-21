<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Resources\RecipeResource;
use App\Http\Resources\RecipeIngredientResource;
use App\Models\Recipe;
use App\Services\Recipe\RecipeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Exception;

class RecipeController extends BaseController
{
    private RecipeService $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    /**
     * Display a listing of recipes for the current store
     */
    public function index(Request $request)
    {
        try {
            $storeId = $request->header('X-Store-ID');
            $activeOnly = $request->query('active_only', true);

            if (!$storeId) {
                return response()->json(['error' => 'Store ID is required'], 400);
            }

            $recipes = $this->recipeService->getStoreRecipes($storeId, $activeOnly);

            return response()->json(RecipeResource::collection($recipes), 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve recipes',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created recipe
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'yield_quantity' => 'required|numeric|min:0.01',
            'yield_unit_id' => 'required|integer|exists:units,id',
            'preparation_time_minutes' => 'nullable|integer|min:0',
            'cooking_time_minutes' => 'nullable|integer|min:0',
            'skill_level' => 'nullable|string|in:beginner,intermediate,advanced,expert',
            'is_active' => 'boolean',
            'store_id' => 'required|integer|exists:stores,id',

            // Ingredients array
            'ingredients' => 'nullable|array',
            'ingredients.*.product_id' => 'required|integer|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
            'ingredients.*.unit_id' => 'required|integer|exists:units,id',
            'ingredients.*.waste_percentage' => 'nullable|numeric|min:0|max:100',
            'ingredients.*.preparation_note' => 'nullable|string|max:255',
            'ingredients.*.is_optional' => 'nullable|boolean',
        ]);

        try {
            $validated['user_id'] = Auth::id();

            $ingredients = $validated['ingredients'] ?? [];
            unset($validated['ingredients']);

            $recipe = $this->recipeService->createRecipe($validated, $ingredients);

            return response()->json(new RecipeResource($recipe), 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create recipe',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified recipe with full cost breakdown
     */
    public function show($id)
    {
        try {
            $recipe = $this->recipeService->getRecipeWithCostBreakdown($id);
            return response()->json(new RecipeResource($recipe), 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Recipe not found'], 404);
        }
    }

    /**
     * Update the specified recipe
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'yield_quantity' => 'sometimes|required|numeric|min:0.01',
            'yield_unit_id' => 'sometimes|required|integer|exists:units,id',
            'preparation_time_minutes' => 'nullable|integer|min:0',
            'cooking_time_minutes' => 'nullable|integer|min:0',
            'skill_level' => 'nullable|string|in:beginner,intermediate,advanced,expert',
            'is_active' => 'boolean',

            // Ingredients array - if provided, replaces all ingredients
            'ingredients' => 'nullable|array',
            'ingredients.*.product_id' => 'required|integer|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
            'ingredients.*.unit_id' => 'required|integer|exists:units,id',
            'ingredients.*.waste_percentage' => 'nullable|numeric|min:0|max:100',
            'ingredients.*.preparation_note' => 'nullable|string|max:255',
            'ingredients.*.is_optional' => 'nullable|boolean',
        ]);

        try {
            $ingredients = isset($validated['ingredients']) ? $validated['ingredients'] : null;
            if (isset($validated['ingredients'])) {
                unset($validated['ingredients']);
            }

            $recipe = $this->recipeService->updateRecipe($id, $validated, $ingredients);

            return response()->json(new RecipeResource($recipe), 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update recipe',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified recipe
     */
    public function destroy($id)
    {
        try {
            $this->recipeService->deleteRecipe($id);
            return response()->json(['message' => 'Recipe deleted successfully'], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to delete recipe',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recalculate cost for a specific recipe
     */
    public function recalculateCost($id)
    {
        try {
            $recipe = Recipe::with(['ingredients.product', 'ingredients.unit'])->findOrFail($id);

            // Recalculate each ingredient cost
            foreach ($recipe->ingredients as $ingredient) {
                $ingredient->calculateCost();
            }

            // Recalculate recipe total
            $recipe->calculateTotalCost();

            return response()->json([
                'message' => 'Recipe cost recalculated successfully',
                'recipe' => new RecipeResource($recipe->fresh(['ingredients.product', 'ingredients.unit']))
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to recalculate recipe cost',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Recalculate costs for all recipes in the store
     */
    public function recalculateAllCosts(Request $request)
    {
        try {
            $storeId = $request->header('X-Store-ID');

            if (!$storeId) {
                return response()->json(['error' => 'Store ID is required'], 400);
            }

            $count = $this->recipeService->recalculateAllRecipeCosts($storeId);

            return response()->json([
                'message' => 'All recipe costs recalculated successfully',
                'recipes_updated' => $count
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to recalculate recipe costs',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clone a recipe
     */
    public function clone(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $userId = Auth::id();
            $recipe = $this->recipeService->cloneRecipe($id, $validated['name'], $userId);

            return response()->json([
                'message' => 'Recipe cloned successfully',
                'recipe' => new RecipeResource($recipe)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to clone recipe',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate profitability for a recipe with a given selling price
     */
    public function calculateProfitability(Request $request, $id)
    {
        $validated = $request->validate([
            'selling_price' => 'required|numeric|min:0.01',
        ]);

        try {
            $profitability = $this->recipeService->calculateProfitability($id, $validated['selling_price']);

            return response()->json($profitability, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to calculate profitability',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add an ingredient to an existing recipe
     */
    public function addIngredient(Request $request, $id)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_id' => 'required|integer|exists:units,id',
            'waste_percentage' => 'nullable|numeric|min:0|max:100',
            'preparation_note' => 'nullable|string|max:255',
            'is_optional' => 'nullable|boolean',
        ]);

        try {
            $ingredient = $this->recipeService->addIngredientToRecipe($id, $validated);

            // Recalculate recipe cost
            $recipe = Recipe::findOrFail($id);
            $recipe->calculateTotalCost();

            return response()->json([
                'message' => 'Ingredient added successfully',
                'ingredient' => new RecipeIngredientResource($ingredient),
                'recipe' => new RecipeResource($recipe->fresh(['ingredients.product', 'ingredients.unit']))
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to add ingredient',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove an ingredient from a recipe
     */
    public function removeIngredient($recipeId, $ingredientId)
    {
        try {
            $this->recipeService->removeIngredientFromRecipe($recipeId, $ingredientId);

            return response()->json(['message' => 'Ingredient removed successfully'], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to remove ingredient',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an ingredient in a recipe
     */
    public function updateIngredient(Request $request, $recipeId, $ingredientId)
    {
        $validated = $request->validate([
            'product_id' => 'sometimes|required|integer|exists:products,id',
            'quantity' => 'sometimes|required|numeric|min:0.01',
            'unit_id' => 'sometimes|required|integer|exists:units,id',
            'waste_percentage' => 'nullable|numeric|min:0|max:100',
            'preparation_note' => 'nullable|string|max:255',
            'is_optional' => 'nullable|boolean',
        ]);

        try {
            $ingredient = $this->recipeService->updateIngredient($ingredientId, $validated);

            return response()->json([
                'message' => 'Ingredient updated successfully',
                'ingredient' => new RecipeIngredientResource($ingredient)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update ingredient',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
