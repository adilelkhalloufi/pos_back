<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'max_users',
        'max_stores',
        'description',
        'features',
        'is_active',
        'trial_days',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get users associated with this plan
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if plan allows creating more users
     */
    public function canCreateUser(int $currentUserCount): bool
    {
        return $currentUserCount < $this->max_users;
    }

    /**
     * Check if plan allows creating more stores
     */
    public function canCreateStore(int $currentStoreCount): bool
    {
        return $currentStoreCount < $this->max_stores;
    }

    /**
     * Get the remaining users that can be created
     */
    public function getRemainingUsers(int $currentUserCount): int
    {
        return max(0, $this->max_users - $currentUserCount);
    }

    /**
     * Get the remaining stores that can be created
     */
    public function getRemainingStores(int $currentStoreCount): int
    {
        return max(0, $this->max_stores - $currentStoreCount);
    }
}
