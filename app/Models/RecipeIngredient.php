<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class RecipeIngredient extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'recipe_ingredients';
    public const COL_ID = 'id';
    public const COL_RECIPE_ID = 'recipe_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_QUANTITY = 'quantity';
    public const COL_UNIT_ID = 'unit_id';
    public const COL_WASTE_PERCENTAGE = 'waste_percentage';
    public const COL_PREPARATION_NOTE = 'preparation_note';
    public const COL_IS_OPTIONAL = 'is_optional';
    public const COL_COST = 'cost';

    protected $fillable = [
        self::COL_RECIPE_ID,
        self::COL_PRODUCT_ID,
        self::COL_QUANTITY,
        self::COL_UNIT_ID,
        self::COL_WASTE_PERCENTAGE,
        self::COL_PREPARATION_NOTE,
        self::COL_IS_OPTIONAL,
        self::COL_COST,
    ];

    protected $casts = [
        self::COL_QUANTITY => 'float',
        self::COL_WASTE_PERCENTAGE => 'float',
        self::COL_COST => 'float',
        self::COL_IS_OPTIONAL => 'boolean',
    ];

    /**
     * Get the recipe this ingredient belongs to
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the product (ingredient) details
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit of measurement
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Calculate the cost of this ingredient including waste and unit conversion
     * Formula: (quantity_converted + (quantity_converted * waste_percentage / 100)) * unit_cost
     * This will be called by RecipeService
     */
    public function calculateCost(): float
    {
        // Get the product's current cost from StoreProducts
        $storeProduct = StoreProducts::where('product_id', $this->product_id)
            ->where('store_id', $this->recipe->store_id)
            ->first();

        if (!$storeProduct || !$storeProduct->cost) {
            // Fallback to product's price_buy if store-specific cost not available
            $costPerUnit = $this->product->price_buy ?? 0;
        } else {
            $costPerUnit = $storeProduct->cost;
        }

        // Get the product's base unit (the unit in which cost is stored)
        $productUnitId = $this->product->unit_id;
        $recipeUnitId = $this->unit_id;

        // Convert quantity to product's unit if necessary
        $quantityInProductUnit = $this->quantity;

        if ($productUnitId !== $recipeUnitId) {
            // Need to convert units
            $conversionService = app(\App\Services\UnitConversion\ConversionService::class);

            try {
                $quantityInProductUnit = $conversionService->convert(
                    $this->quantity,
                    $recipeUnitId,
                    $productUnitId,
                    $this->recipe->store_id
                );
            } catch (\Exception $e) {
                // If conversion fails, log warning and use original quantity
                Log::warning("Unit conversion failed for ingredient {$this->product->name}: " . $e->getMessage());
                // Fall back to no conversion (will give incorrect cost, but won't break)
                $quantityInProductUnit = $this->quantity;
            }
        }

        // Calculate quantity with waste factored in
        $wasteMultiplier = 1 + (($this->waste_percentage ?? 0) / 100);
        $effectiveQuantity = $quantityInProductUnit * $wasteMultiplier;

        // Calculate total cost
        $totalCost = $effectiveQuantity * $costPerUnit;

        // Update the cost field
        $this->update([self::COL_COST => $totalCost]);

        return $totalCost;
    }

    /**
     * Get the effective quantity including waste
     */
    public function getEffectiveQuantityAttribute(): float
    {
        return $this->quantity * (1 + ($this->waste_percentage / 100));
    }
}
