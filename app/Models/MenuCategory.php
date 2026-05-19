<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class MenuCategory extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'menu_categories';
    public const COL_ID = 'id';
    public const COL_MENU_ID = 'menu_id';
    public const COL_NAME = 'name';
    public const COL_DESCRIPTION = 'description';
    public const COL_DISPLAY_ORDER = 'display_order';
    public const COL_IS_ACTIVE = 'is_active';

    protected $fillable = [
        self::COL_MENU_ID,
        self::COL_NAME,
        self::COL_DESCRIPTION,
        self::COL_DISPLAY_ORDER,
        self::COL_IS_ACTIVE,
    ];

    protected $casts = [
        self::COL_IS_ACTIVE => 'boolean',
        self::COL_DISPLAY_ORDER => 'integer',
    ];

    /**
     * Get the parent menu
     */
    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    /**
     * Get the menu items in this category
     */
    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy(MenuItem::COL_DISPLAY_ORDER);
    }

    /**
     * Get only active items
     */
    public function activeItems()
    {
        return $this->hasMany(MenuItem::class)
            ->where(MenuItem::COL_IS_ACTIVE, true)
            ->orderBy(MenuItem::COL_DISPLAY_ORDER);
    }

    /**
     * Get only available items (active and in stock)
     */
    public function availableItems()
    {
        return $this->hasMany(MenuItem::class)
            ->where(MenuItem::COL_IS_ACTIVE, true)
            ->where(MenuItem::COL_IS_AVAILABLE, true)
            ->orderBy(MenuItem::COL_DISPLAY_ORDER);
    }

    /**
     * Scope to get only active categories
     */
    public function scopeActive($query)
    {
        return $query->where(self::COL_IS_ACTIVE, true);
    }

    /**
     * Scope to get categories for a specific menu
     */
    public function scopeForMenu($query, $menuId)
    {
        return $query->where(self::COL_MENU_ID, $menuId);
    }
}
