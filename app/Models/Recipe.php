<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Recipe extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'recipes';
    public const COL_ID = 'id';
    public const COL_NAME = 'name';
    public const COL_DESCRIPTION = 'description';
    public const COL_INSTRUCTIONS = 'instructions';
    public const COL_YIELD_QUANTITY = 'yield_quantity';
    public const COL_YIELD_UNIT_ID = 'yield_unit_id';
    public const COL_PREPARATION_TIME_MINUTES = 'preparation_time_minutes';
    public const COL_COOKING_TIME_MINUTES = 'cooking_time_minutes';
    public const COL_SKILL_LEVEL = 'skill_level';
    public const COL_TOTAL_COST = 'total_cost';
    public const COL_COST_PER_SERVING = 'cost_per_serving';
    public const COL_VERSION = 'version';
    public const COL_IS_ACTIVE = 'is_active';
    public const COL_STORE_ID = 'store_id';
    public const COL_USER_ID = 'user_id';

    protected $fillable = [
        self::COL_NAME,
        self::COL_DESCRIPTION,
        self::COL_INSTRUCTIONS,
        self::COL_YIELD_QUANTITY,
        self::COL_YIELD_UNIT_ID,
        self::COL_PREPARATION_TIME_MINUTES,
        self::COL_COOKING_TIME_MINUTES,
        self::COL_SKILL_LEVEL,
        self::COL_TOTAL_COST,
        self::COL_COST_PER_SERVING,
        self::COL_VERSION,
        self::COL_IS_ACTIVE,
        self::COL_STORE_ID,
        self::COL_USER_ID,
    ];

    protected $casts = [
        self::COL_YIELD_QUANTITY => 'float',
        self::COL_PREPARATION_TIME_MINUTES => 'integer',
        self::COL_COOKING_TIME_MINUTES => 'integer',
        self::COL_TOTAL_COST => 'float',
        self::COL_COST_PER_SERVING => 'float',
        self::COL_VERSION => 'integer',
        self::COL_IS_ACTIVE => 'boolean',
    ];

    /**
     * Get the recipe ingredients (BOM)
     */
    public function ingredients()
    {
        return $this->hasMany(RecipeIngredient::class);
    }

    /**
     * Get the yield unit
     */
    public function yieldUnit()
    {
        return $this->belongsTo(Unit::class, self::COL_YIELD_UNIT_ID);
    }

    /**
     * Get the store this recipe belongs to
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the user who created this recipe
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total recipe cost from ingredients
     * This will be called by RecipeService
     */
    public function calculateTotalCost(): float
    {
        $totalCost = $this->ingredients()->sum(RecipeIngredient::COL_COST);

        $this->update([
            self::COL_TOTAL_COST => $totalCost,
            self::COL_COST_PER_SERVING => $this->yield_quantity > 0
                ? $totalCost / $this->yield_quantity
                : $totalCost,
        ]);

        return $totalCost;
    }

    /**
     * Get total preparation and cooking time
     */
    public function getTotalTimeAttribute(): int
    {
        return ($this->preparation_time_minutes ?? 0) + ($this->cooking_time_minutes ?? 0);
    }
}
