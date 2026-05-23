<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuItem extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'menu_items';
    public const COL_ID = 'id';
    public const COL_MENU_CATEGORY_ID = 'menu_category_id';
    public const COL_NAME = 'name';
    public const COL_DESCRIPTION = 'description';
    public const COL_IMAGE = 'image';
    public const COL_PRICE = 'price';
    public const COL_COST = 'cost';
    public const COL_IS_ACTIVE = 'is_active';
    public const COL_IS_AVAILABLE = 'is_available';
    public const COL_PREPARATION_TIME_MINUTES = 'preparation_time_minutes';
    public const COL_ITEM_TYPE = 'item_type';
    public const COL_RECIPE_ID = 'recipe_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_STORE_ID = 'store_id';
    public const COL_DISPLAY_ORDER = 'display_order';

    public const ITEM_TYPE_RECIPE = 'recipe';
    public const ITEM_TYPE_COMBO = 'combo';
    public const ITEM_TYPE_SIMPLE = 'simple';
    public const ITEM_TYPE_PRODUCT = 'product';

    protected $fillable = [
        self::COL_MENU_CATEGORY_ID,
        self::COL_NAME,
        self::COL_DESCRIPTION,
        self::COL_IMAGE,
        self::COL_PRICE,
        self::COL_COST,
        self::COL_IS_ACTIVE,
        self::COL_IS_AVAILABLE,
        self::COL_PREPARATION_TIME_MINUTES,
        self::COL_ITEM_TYPE,
        self::COL_RECIPE_ID,
        self::COL_PRODUCT_ID,
        self::COL_STORE_ID,
        self::COL_DISPLAY_ORDER,
    ];

    protected $casts = [
        self::COL_PRICE => 'float',
        self::COL_COST => 'float',
        self::COL_IS_ACTIVE => 'boolean',
        self::COL_IS_AVAILABLE => 'boolean',
        self::COL_PREPARATION_TIME_MINUTES => 'integer',
        self::COL_DISPLAY_ORDER => 'integer',
    ];

    /**
     * Get the parent category
     */
    public function category()
    {
        return $this->belongsTo(MenuCategory::class, self::COL_MENU_CATEGORY_ID);
    }

    /**
     * Get the linked recipe
     */
    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Get the linked product (for product-based menu items)
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the store this menu item belongs to
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Calculate food cost percentage
     */
    public function getFoodCostPercentageAttribute(): float
    {
        if ($this->price <= 0) {
            return 0;
        }

        return round(($this->cost / $this->price) * 100, 2);
    }

    /**
     * Calculate profit margin
     */
    public function getProfitMarginAttribute(): float
    {
        return $this->price - $this->cost;
    }

    /**
     * Calculate profit margin percentage
     */
    public function getProfitMarginPercentageAttribute(): float
    {
        if ($this->price <= 0) {
            return 0;
        }

        return round((($this->price - $this->cost) / $this->price) * 100, 2);
    }

    /**
     * Update cost from linked recipe
     */
    public function updateCostFromRecipe(): bool
    {
        if ($this->item_type !== self::ITEM_TYPE_RECIPE || !$this->recipe_id) {
            return false;
        }

        $recipe = $this->recipe;
        if (!$recipe) {
            return false;
        }

        $this->update([
            self::COL_COST => $recipe->total_cost,
        ]);

        return true;
    }

    /**
     * Update cost from linked product
     */
    public function updateCostFromProduct(): bool
    {
        if ($this->item_type !== self::ITEM_TYPE_PRODUCT || !$this->product_id) {
            return false;
        }

        $product = $this->product;
        if (!$product) {
            return false;
        }

        // Use product's purchase price as cost
        $this->update([
            self::COL_COST => $product->price_buy ?? 0,
        ]);

        return true;
    }

    /**
     * Scope to get only active items
     */
    public function scopeActive($query)
    {
        return $query->where(self::COL_IS_ACTIVE, true);
    }

    /**
     * Scope to get only available items (active and in stock)
     */
    public function scopeAvailable($query)
    {
        return $query->where(self::COL_IS_ACTIVE, true)
            ->where(self::COL_IS_AVAILABLE, true);
    }

    /**
     * Scope to get items for a specific store
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where(self::COL_STORE_ID, $storeId);
    }

    /**
     * Scope to get items by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where(self::COL_ITEM_TYPE, $type);
    }

    /**
     * Scope to get recipe-based items
     */
    public function scopeRecipeBased($query)
    {
        return $query->where(self::COL_ITEM_TYPE, self::ITEM_TYPE_RECIPE);
    }
}
