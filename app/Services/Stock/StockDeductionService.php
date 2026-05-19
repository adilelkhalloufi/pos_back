<?php

namespace App\Services\Stock;

use App\Models\MenuItem;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\StoreProducts;
use App\Models\StockMovement;
use App\Models\TheoreticalConsumption;
use App\Services\UnitConversion\ConversionService;
use App\Services\Costing\CostingService;
use Exception;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * StockDeductionService - Handles automatic ingredient deduction from inventory
 * when menu items are sold through POS
 */
class StockDeductionService
{
    protected $conversionService;
    protected $costingService;

    public function __construct(
        ConversionService $conversionService,
        CostingService $costingService
    ) {
        $this->conversionService = $conversionService;
        $this->costingService = $costingService;
    }

    /**
     * Deduct stock for a menu item sale
     * 
     * @param int $menuItemId - Menu item sold
     * @param float $quantity - Quantity of menu items sold
     * @param int $storeId - Store where sale occurred
     * @param int $userId - User who made the sale
     * @param mixed $orderSaleId - Reference to order sale
     * @return array - Deduction results
     * @throws Exception if insufficient stock
     */
    public function deductMenuItemStock(
        int $menuItemId,
        float $quantity,
        int $storeId,
        int $userId,
        $orderSaleId = null
    ): array {
        DB::beginTransaction();

        try {
            $menuItem = MenuItem::with(['recipe.ingredients.product.unit'])->findOrFail($menuItemId);

            // Check if menu item has a recipe
            if (!$menuItem->recipe_id) {
                throw new Exception("Menu item '{$menuItem->name}' has no recipe attached.");
            }

            $recipe = $menuItem->recipe;

            if (!$recipe) {
                throw new Exception("Recipe not found for menu item '{$menuItem->name}'.");
            }

            // Deduct all ingredients
            $deductions = [];

            foreach ($recipe->ingredients as $ingredient) {
                $deduction = $this->deductIngredient(
                    $ingredient,
                    $quantity,
                    $storeId,
                    $userId,
                    $orderSaleId,
                    $menuItem
                );

                $deductions[] = $deduction;
            }

            DB::commit();

            return [
                'success' => true,
                'menu_item' => $menuItem->name,
                'quantity_sold' => $quantity,
                'recipe' => $recipe->name,
                'deductions' => $deductions,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Deduct a single ingredient from stock
     * 
     * @param RecipeIngredient $ingredient
     * @param float $multiplier - Number of times recipe is made
     * @param int $storeId
     * @param int $userId
     * @param mixed $orderSaleId
     * @param MenuItem $menuItem
     * @return array
     * @throws Exception
     */
    protected function deductIngredient(
        RecipeIngredient $ingredient,
        float $multiplier,
        int $storeId,
        int $userId,
        $orderSaleId,
        MenuItem $menuItem
    ): array {
        $product = $ingredient->product;

        // Calculate quantity needed (including waste percentage)
        $quantityNeeded = $ingredient->quantity_with_waste * $multiplier;

        // Get store product
        $storeProduct = StoreProducts::where('product_id', $product->id)
            ->where('store_id', $storeId)
            ->first();

        if (!$storeProduct) {
            throw new Exception("Product '{$product->name}' not available in store.");
        }

        // Check if unit conversion is needed
        $quantityToDeduct = $quantityNeeded;
        $stockUnit = $product->unit_id;
        $recipeUnit = $ingredient->unit_id;

        if ($stockUnit !== $recipeUnit) {
            // Convert recipe unit to stock unit
            try {
                $quantityToDeduct = $this->conversionService->convert(
                    $quantityNeeded,
                    $recipeUnit,
                    $stockUnit,
                    $storeId
                );
            } catch (Exception $e) {
                throw new Exception(
                    "Cannot convert units for product '{$product->name}': " . $e->getMessage()
                );
            }
        }

        // Check if sufficient stock
        if ($storeProduct->stock < $quantityToDeduct) {
            throw new Exception(
                "Insufficient stock for '{$product->name}'. Required: {$quantityToDeduct}, Available: {$storeProduct->stock}"
            );
        }

        // Get current weighted average cost
        $unitCost = $this->costingService->getCurrentAverageCost($product->id, $storeId);

        // Deduct from stock
        $storeProduct->stock -= $quantityToDeduct;
        $storeProduct->save();

        // Create stock movement
        $stockMovement = StockMovement::create([
            'product_id' => $product->id,
            'store_id' => $storeId,
            'type' => 'sale',
            'direction' => 'out',
            'quantity' => $quantityToDeduct,
            'unit_cost' => $unitCost,
            'total_cost' => $quantityToDeduct * $unitCost,
            'user_id' => $userId,
            'referenceable_type' => 'App\\Models\\OrderSale',
            'referenceable_id' => $orderSaleId,
            'notes' => "Auto-deduction for menu item: {$menuItem->name}",
        ]);

        // Update theoretical consumption
        $this->updateTheoreticalConsumption(
            $product->id,
            $storeId,
            $quantityToDeduct,
            now()->toDateString()
        );

        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity_deducted' => $quantityToDeduct,
            'unit_cost' => $unitCost,
            'total_cost' => $quantityToDeduct * $unitCost,
            'remaining_stock' => $storeProduct->stock,
            'stock_movement_id' => $stockMovement->id,
        ];
    }

    /**
     * Update or create theoretical consumption record
     * 
     * @param int $productId
     * @param int $storeId
     * @param float $quantity
     * @param string $date
     * @return void
     */
    protected function updateTheoreticalConsumption(
        int $productId,
        int $storeId,
        float $quantity,
        string $date
    ): void {
        $consumption = TheoreticalConsumption::firstOrNew([
            'product_id' => $productId,
            'store_id' => $storeId,
            'date' => $date,
        ]);

        // Add to theoretical quantity
        $consumption->theoretical_quantity += $quantity;

        // Calculate variance if we have actual quantity
        $consumption->calculateVariance();

        $consumption->save();
    }

    /**
     * Check if a menu item can be sold (sufficient stock for all ingredients)
     * 
     * @param int $menuItemId
     * @param float $quantity
     * @param int $storeId
     * @return array - ['available' => bool, 'missing_items' => array]
     */
    public function checkMenuItemAvailability(int $menuItemId, float $quantity, int $storeId): array
    {
        $menuItem = MenuItem::with(['recipe.ingredients.product.unit'])->findOrFail($menuItemId);

        if (!$menuItem->recipe_id) {
            return [
                'available' => false,
                'reason' => 'No recipe attached',
                'missing_items' => [],
            ];
        }

        $recipe = $menuItem->recipe;
        $missingItems = [];

        foreach ($recipe->ingredients as $ingredient) {
            $product = $ingredient->product;
            $quantityNeeded = $ingredient->quantity_with_waste * $quantity;

            $storeProduct = StoreProducts::where('product_id', $product->id)
                ->where('store_id', $storeId)
                ->first();

            if (!$storeProduct) {
                $missingItems[] = [
                    'product_name' => $product->name,
                    'reason' => 'Product not available in store',
                ];
                continue;
            }

            // Check if unit conversion is needed
            $quantityToCheck = $quantityNeeded;
            $stockUnit = $product->unit_id;
            $recipeUnit = $ingredient->unit_id;

            if ($stockUnit !== $recipeUnit) {
                try {
                    $quantityToCheck = $this->conversionService->convert(
                        $quantityNeeded,
                        $recipeUnit,
                        $stockUnit,
                        $storeId
                    );
                } catch (Exception $e) {
                    $missingItems[] = [
                        'product_name' => $product->name,
                        'reason' => 'Unit conversion not configured',
                    ];
                    continue;
                }
            }

            if ($storeProduct->stock < $quantityToCheck) {
                $missingItems[] = [
                    'product_name' => $product->name,
                    'required' => $quantityToCheck,
                    'available' => $storeProduct->stock,
                    'shortage' => $quantityToCheck - $storeProduct->stock,
                ];
            }
        }

        return [
            'available' => empty($missingItems),
            'missing_items' => $missingItems,
        ];
    }

    /**
     * Simulate stock deduction without actually deducting (for preview)
     * 
     * @param int $menuItemId
     * @param float $quantity
     * @param int $storeId
     * @return array
     */
    public function simulateDeduction(int $menuItemId, float $quantity, int $storeId): array
    {
        $menuItem = MenuItem::with(['recipe.ingredients.product.unit'])->findOrFail($menuItemId);

        if (!$menuItem->recipe_id) {
            throw new Exception("Menu item '{$menuItem->name}' has no recipe attached.");
        }

        $recipe = $menuItem->recipe;
        $simulation = [];

        foreach ($recipe->ingredients as $ingredient) {
            $product = $ingredient->product;
            $quantityNeeded = $ingredient->quantity_with_waste * $quantity;

            $storeProduct = StoreProducts::where('product_id', $product->id)
                ->where('store_id', $storeId)
                ->first();

            $quantityToDeduct = $quantityNeeded;
            $stockUnit = $product->unit_id;
            $recipeUnit = $ingredient->unit_id;

            if ($stockUnit !== $recipeUnit) {
                try {
                    $quantityToDeduct = $this->conversionService->convert(
                        $quantityNeeded,
                        $recipeUnit,
                        $stockUnit,
                        $storeId
                    );
                } catch (Exception $e) {
                    $quantityToDeduct = null;
                }
            }

            $unitCost = $this->costingService->getCurrentAverageCost($product->id, $storeId);

            $simulation[] = [
                'product_name' => $product->name,
                'quantity_needed' => $quantityNeeded,
                'quantity_to_deduct' => $quantityToDeduct,
                'current_stock' => $storeProduct ? $storeProduct->stock : 0,
                'remaining_stock' => $storeProduct && $quantityToDeduct ? ($storeProduct->stock - $quantityToDeduct) : null,
                'unit_cost' => $unitCost,
                'total_cost' => $quantityToDeduct ? ($quantityToDeduct * $unitCost) : null,
                'sufficient' => $storeProduct && $quantityToDeduct ? ($storeProduct->stock >= $quantityToDeduct) : false,
            ];
        }

        return [
            'menu_item' => $menuItem->name,
            'quantity' => $quantity,
            'recipe' => $recipe->name,
            'ingredients' => $simulation,
            'total_cost' => array_sum(array_column(array_filter($simulation, fn($i) => $i['total_cost']), 'total_cost')),
        ];
    }
}
