# Phase 1 Quick Reference - Recipe & Costing System

## API Service Usage Guide

### RecipeService Usage

```php
use App\Services\Recipe\RecipeService;

$recipeService = app(RecipeService::class);

// Create Recipe
$recipe = $recipeService->createRecipe(
    recipeData: [
        'name' => 'Recipe Name',
        'description' => 'Description',
        'yield_quantity' => 4,
        'yield_unit_id' => 1,
        'store_id' => 1,
        'user_id' => auth()->id(),
    ],
    ingredients: [
        [
            'product_id' => 1,
            'quantity' => 100,
            'unit_id' => 1,
            'waste_percentage' => 10,
        ],
    ]
);

// Update Recipe
$recipe = $recipeService->updateRecipe(
    recipeId: 1,
    recipeData: ['name' => 'Updated Name'],
    ingredients: null // or provide new array to replace all
);

// Get Recipe with Cost Breakdown
$recipe = $recipeService->getRecipeWithCostBreakdown(recipeId: 1);

// Calculate Profitability
$profitability = $recipeService->calculateProfitability(
    recipeId: 1,
    sellingPrice: 15.99
);

// Clone Recipe
$newRecipe = $recipeService->cloneRecipe(
    recipeId: 1,
    newName: 'Copy of Original',
    userId: auth()->id()
);
```

### CostingService Usage

```php
use App\Services\Costing\CostingService;

$costingService = app(CostingService::class);

// Calculate Weighted Average on Stock Movement
$costData = $costingService->calculateWeightedAverageCost(
    productId: 1,
    storeId: 1,
    movementQuantity: 10, // positive for IN, negative for OUT
    unitCost: 5.50,
    movementType: 'purchase' // or 'sale', 'transfer_in', etc.
);

// Get Current Average Cost
$avgCost = $costingService->getCurrentAverageCost(
    productId: 1,
    storeId: 1
);

// Get Total Inventory Value
$totalValue = $costingService->getTotalInventoryValue(storeId: 1);

// Calculate COGS for Period
$cogs = $costingService->calculateCOGS(
    storeId: 1,
    startDate: '2026-03-01',
    endDate: '2026-03-31'
);
```

## Model Usage

### Recipe Model

```php
use App\Models\Recipe;

// Create
$recipe = Recipe::create([
    'name' => 'Recipe Name',
    'yield_quantity' => 4,
    'store_id' => 1,
    'user_id' => 1,
]);

// Calculate Cost
$recipe->calculateTotalCost();

// Relationships
$recipe->ingredients; // Get all ingredients
$recipe->yieldUnit; // Get yield unit
$recipe->store; // Get store
```

### RecipeIngredient Model

```php
use App\Models\RecipeIngredient;

// Create
$ingredient = RecipeIngredient::create([
    'recipe_id' => 1,
    'product_id' => 10,
    'quantity' => 100,
    'unit_id' => 1,
    'waste_percentage' => 5,
]);

// Calculate Cost
$ingredient->calculateCost();

// Get Effective Quantity (with waste)
$effectiveQty = $ingredient->effective_quantity;
```

## Database Constants

### Recipe Constants

```php
Recipe::COL_ID
Recipe::COL_NAME
Recipe::COL_TOTAL_COST
Recipe::COL_COST_PER_SERVING
Recipe::COL_IS_ACTIVE
Recipe::COL_STORE_ID
```

### RecipeIngredient Constants

```php
RecipeIngredient::COL_RECIPE_ID
RecipeIngredient::COL_PRODUCT_ID
RecipeIngredient::COL_QUANTITY
RecipeIngredient::COL_COST
RecipeIngredient::COL_WASTE_PERCENTAGE
```

### Store Constants (New)

```php
Store::COL_COSTING_METHOD
Store::COL_TARGET_FOOD_COST_PERCENTAGE
```

## Cost Calculation Formulas

### Ingredient Cost with Waste

```php
effective_quantity = quantity × (1 + waste_percentage / 100)
ingredient_cost = effective_quantity × unit_cost
```

### Recipe Total Cost

```php
total_cost = SUM(all_ingredient_costs)
cost_per_serving = total_cost / yield_quantity
```

### Weighted Average Cost

```php
// When adding stock (purchase)
new_average = (old_total_value + new_purchase_value) / (old_qty + new_qty)

// When removing stock (sale)
new_average = old_average (stays the same)
new_value = new_stock × average_cost
```

### Profitability Metrics

```php
gross_profit = selling_price - cost_per_serving
profit_margin_% = (gross_profit / selling_price) × 100
food_cost_% = (cost_per_serving / selling_price) × 100
```

## Integration Points

### Stock Movement Integration

When creating stock movements, call CostingService:

```php
$costData = $costingService->calculateWeightedAverageCost(
    productId: $product->id,
    storeId: $store->id,
    movementQuantity: $quantity,
    unitCost: $unitCost,
    movementType: $type
);

StockMovement::create([
    'product_id' => $product->id,
    'store_id' => $store->id,
    'quantity' => $quantity,
    'unit_cost' => $unitCost,
    'average_cost_before' => $costData['average_cost_before'],
    'average_cost_after' => $costData['average_cost_after'],
    'total_value_before' => $costData['total_value_before'],
    'total_value_after' => $costData['total_value_after'],
    // ... other fields
]);
```

### Price Change Integration

Already integrated in PriceChangeService - recipe costs update automatically when product prices change.

## Testing Examples

### Test Recipe Creation

```bash
php artisan tinker

$recipe = \App\Services\Recipe\RecipeService::class::createRecipe(
    ['name' => 'Test', 'store_id' => 1, 'user_id' => 1, 'yield_quantity' => 1],
    [['product_id' => 1, 'quantity' => 100, 'unit_id' => 1]]
);
```

### Test Cost Calculation

```bash
$costing = app(\App\Services\Costing\CostingService::class);
$value = $costing->getTotalInventoryValue(1);
echo "Total inventory value: $$value";
```

## Next: Creating Controllers/API Endpoints

Example controller structure:

```php
namespace App\Http\Controllers\Api;

use App\Services\Recipe\RecipeService;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    protected $recipeService;

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'yield_quantity' => 'required|numeric|min:0.01',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $recipe = $this->recipeService->createRecipe(
            $validated,
            $validated['ingredients']
        );

        return response()->json($recipe, 201);
    }
}
```
