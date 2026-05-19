<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Menu extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'menus';
    public const COL_ID = 'id';
    public const COL_NAME = 'name';
    public const COL_DESCRIPTION = 'description';
    public const COL_TYPE = 'type';
    public const COL_IS_ACTIVE = 'is_active';
    public const COL_DISPLAY_ORDER = 'display_order';
    public const COL_AVAILABLE_FROM_TIME = 'available_from_time';
    public const COL_AVAILABLE_TO_TIME = 'available_to_time';
    public const COL_STORE_ID = 'store_id';

    public const TYPE_BREAKFAST = 'breakfast';
    public const TYPE_LUNCH = 'lunch';
    public const TYPE_DINNER = 'dinner';
    public const TYPE_DRINKS = 'drinks';
    public const TYPE_ALL_DAY = 'all_day';

    protected $fillable = [
        self::COL_NAME,
        self::COL_DESCRIPTION,
        self::COL_TYPE,
        self::COL_IS_ACTIVE,
        self::COL_DISPLAY_ORDER,
        self::COL_AVAILABLE_FROM_TIME,
        self::COL_AVAILABLE_TO_TIME,
        self::COL_STORE_ID,
    ];

    protected $casts = [
        self::COL_IS_ACTIVE => 'boolean',
        self::COL_DISPLAY_ORDER => 'integer',
    ];

    /**
     * Get the menu categories
     */
    public function categories()
    {
        return $this->hasMany(MenuCategory::class)->orderBy(MenuCategory::COL_DISPLAY_ORDER);
    }

    /**
     * Get the store this menu belongs to
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Check if menu is currently available based on time window
     */
    public function isCurrentlyAvailable(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // If no time restrictions, it's always available
        if (empty($this->available_from_time) && empty($this->available_to_time)) {
            return true;
        }

        $currentTime = now()->format('H:i:s');

        if (!empty($this->available_from_time) && $currentTime < $this->available_from_time) {
            return false;
        }

        if (!empty($this->available_to_time) && $currentTime > $this->available_to_time) {
            return false;
        }

        return true;
    }

    /**
     * Scope to get only active menus
     */
    public function scopeActive($query)
    {
        return $query->where(self::COL_IS_ACTIVE, true);
    }

    /**
     * Scope to get menus for a specific store
     */
    public function scopeForStore($query, $storeId)
    {
        return $query->where(self::COL_STORE_ID, $storeId);
    }

    /**
     * Scope to get currently available menus (based on time)
     */
    public function scopeCurrentlyAvailable($query)
    {
        $currentTime = now()->format('H:i:s');

        return $query->where(self::COL_IS_ACTIVE, true)
            ->where(function ($q) use ($currentTime) {
                $q->where(function ($query) use ($currentTime) {
                    // No time restrictions
                    $query->whereNull(self::COL_AVAILABLE_FROM_TIME)
                        ->whereNull(self::COL_AVAILABLE_TO_TIME);
                })
                ->orWhere(function ($query) use ($currentTime) {
                    // Within time window
                    $query->where(self::COL_AVAILABLE_FROM_TIME, '<=', $currentTime)
                        ->where(self::COL_AVAILABLE_TO_TIME, '>=', $currentTime);
                });
            });
    }
}
