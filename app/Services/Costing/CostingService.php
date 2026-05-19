<?php

namespace App\Services\Costing;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Store;
use App\Models\StoreProducts;
use Illuminate\Support\Facades\DB;

/**
 * CostingService - Handles inventory cost calculations
 * 
 * Implements Weighted Average Cost method for restaurant inventory valuation
 * Formula: New Average Cost = (Old Total Value + New Purchase Value) / (Old Qty + New Qty)
 */
class CostingService
{
    /**
     * Calculate and update weighted average cost for a product after a stock movement
     * 
     * @param int $productId
     * @param int $storeId
     * @param float $movementQuantity - Quantity being added (positive) or removed (negative)
     * @param float $unitCost - Cost per unit for this movement
     * @param string $movementType - Type of movement (purchase, sale, transfer, adjustment, etc.)
     * @return array ['average_cost_before', 'average_cost_after', 'total_value_before', 'total_value_after']
     */
    public function calculateWeightedAverageCost(
        int $productId,
        int $storeId,
        float $movementQuantity,
        float $unitCost,
        string $movementType
    ): array {
        // Get current stock and cost from store_products
        $storeProduct = StoreProducts::where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();

        if (!$storeProduct) {
            // Product doesn't exist in this store yet, create it
            $storeProduct = StoreProducts::create([
                'product_id' => $productId,
                'store_id' => $storeId,
                'stock' => 0,
                'cost' => 0,
                'price' => 0,
            ]);
        }

        $currentStock = $storeProduct->stock ?? 0;
        $currentAverageCost = $storeProduct->cost ?? 0;
        $currentTotalValue = $currentStock * $currentAverageCost;

        // Calculate new values based on movement type
        $newStock = $currentStock;
        $newAverageCost = $currentAverageCost;
        $newTotalValue = $currentTotalValue;

        if ($movementType === 'purchase' || $movementType === 'transfer_in' || $movementType === 'adjustment_increase') {
            // Adding stock: Calculate new weighted average
            $newStock = $currentStock + $movementQuantity;
            $addedValue = $movementQuantity * $unitCost;
            $newTotalValue = $currentTotalValue + $addedValue;

            if ($newStock > 0) {
                $newAverageCost = $newTotalValue / $newStock;
            }
        } elseif ($movementType === 'sale' || $movementType === 'transfer_out' || $movementType === 'adjustment_decrease' || $movementType === 'waste') {
            // Removing stock: Average cost stays the same, but total value decreases
            $newStock = $currentStock - abs($movementQuantity);
            $newAverageCost = $currentAverageCost; // Cost doesn't change on sales
            $newTotalValue = $newStock * $newAverageCost;
        }

        // Prevent negative stock
        if ($newStock < 0) {
            $newStock = 0;
            $newTotalValue = 0;
        }

        // Update store product with new cost and stock
        $storeProduct->update([
            'cost' => $newAverageCost,
            'stock' => $newStock,
        ]);

        return [
            'average_cost_before' => round($currentAverageCost, 4),
            'average_cost_after' => round($newAverageCost, 4),
            'total_value_before' => round($currentTotalValue, 4),
            'total_value_after' => round($newTotalValue, 4),
        ];
    }

    /**
     * Get current average cost for a product in a store
     * 
     * @param int $productId
     * @param int $storeId
     * @return float
     */
    public function getCurrentAverageCost(int $productId, int $storeId): float
    {
        $storeProduct = StoreProducts::where('product_id', $productId)
            ->where('store_id', $storeId)
            ->first();

        return $storeProduct->cost ?? 0;
    }

    /**
     * Get total inventory value for a store
     * 
     * @param int $storeId
     * @return float
     */
    public function getTotalInventoryValue(int $storeId): float
    {
        return StoreProducts::where('store_id', $storeId)
            ->selectRaw('SUM(stock * cost) as total_value')
            ->value('total_value') ?? 0;
    }

    /**
     * Get inventory value by category for a store
     * 
     * @param int $storeId
     * @return array
     */
    public function getInventoryValueByCategory(int $storeId): array
    {
        return DB::table('store_products')
            ->join('products', 'store_products.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('store_products.store_id', $storeId)
            ->select(
                'categories.id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(store_products.stock * store_products.cost) as total_value'),
                DB::raw('SUM(store_products.stock) as total_quantity')
            )
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->toArray();
    }

    /**
     * Calculate COGS (Cost of Goods Sold) for a period
     * Formula: COGS = Opening Inventory + Purchases - Closing Inventory
     * 
     * @param int $storeId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function calculateCOGS(int $storeId, string $startDate, string $endDate): array
    {
        // Get opening inventory value (inventory at start of period)
        $openingInventory = $this->getInventoryValueAtDate($storeId, $startDate);

        // Get total purchases during period
        $purchases = StockMovement::where('store_id', $storeId)
            ->where('type', 'purchase')
            ->where('direction', 'in')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_cost');

        // Get closing inventory value (inventory at end of period)
        $closingInventory = $this->getInventoryValueAtDate($storeId, $endDate);

        // Calculate COGS
        $cogs = $openingInventory + $purchases - $closingInventory;

        return [
            'opening_inventory' => round($openingInventory, 2),
            'purchases' => round($purchases, 2),
            'closing_inventory' => round($closingInventory, 2),
            'cogs' => round($cogs, 2),
        ];
    }

    /**
     * Get inventory value at a specific date
     * 
     * @param int $storeId
     * @param string $date
     * @return float
     */
    private function getInventoryValueAtDate(int $storeId, string $date): float
    {
        // This is a simplified version - in production, you'd want to track historical values
        // For now, if date is today, return current value
        // Otherwise, calculate from stock movements up to that date

        if (date('Y-m-d', strtotime($date)) === date('Y-m-d')) {
            return $this->getTotalInventoryValue($storeId);
        }

        // TODO: Implement historical inventory value calculation
        // This would require rebuilding inventory state from stock movements up to the date
        return 0;
    }

    /**
     * Get costing method for a store
     * 
     * @param int $storeId
     * @return string
     */
    public function getCostingMethod(int $storeId): string
    {
        $store = Store::find($storeId);
        return $store->costing_method ?? 'weighted_average';
    }

    /**
     * Recalculate costs for all recipes using a specific product
     * Called when product price changes
     * Also updates menu items linked to affected recipes
     * 
     * @param int $productId
     * @return array ['recipes_updated', 'menu_items_updated']
     */
    public function recalculateRecipeCostsForProduct(int $productId): array
    {
        $recipeIngredients = \App\Models\RecipeIngredient::where('product_id', $productId)->get();

        $updatedRecipes = [];

        foreach ($recipeIngredients as $ingredient) {
            // Recalculate ingredient cost
            $ingredient->calculateCost();

            // Recalculate recipe total cost
            if (!in_array($ingredient->recipe_id, $updatedRecipes)) {
                $ingredient->recipe->calculateTotalCost();
                $updatedRecipes[] = $ingredient->recipe_id;
            }
        }

        // Update menu items linked to affected recipes
        $menuItemsUpdated = 0;
        foreach ($updatedRecipes as $recipeId) {
            $menuItems = \App\Models\MenuItem::where('recipe_id', $recipeId)
                ->where('item_type', \App\Models\MenuItem::ITEM_TYPE_RECIPE)
                ->get();

            foreach ($menuItems as $menuItem) {
                if ($menuItem->updateCostFromRecipe()) {
                    $menuItemsUpdated++;
                }
            }
        }

        return [
            'recipes_updated' => count($updatedRecipes),
            'menu_items_updated' => $menuItemsUpdated,
        ];
    }
}
