<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\ROLES;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends BaseModel implements AuthenticatableContract, FilamentUser
{
    public function canAccessPanel(Panel $panel): bool
    {
        return str_ends_with($this->email, '@adev.ma') && $this->email_verified_at !== null;
    }

    public const TABLE_NAME = 'users';

    public const COL_ID = 'id';

    public const COL_NAME = 'name';

    public const COL_EMAIL = 'email';

    public const COL_PHONE = 'phone';

    public const COL_EMAIL_VERIFIED_AT = 'email_verified_at';

    public const COL_TRIAL_ENDS_AT = 'trial_ends_at';

    public const COL_PASSWORD = 'password';

    public const COL_ROLE = 'role';

    public const COL_REMEMBER_TOKEN = 'remember_token';

    public const COL_CREATED_AT = 'created_at';

    public const COL_UPDATED_AT = 'updated_at';

    public const COL_DESCRIPTION = 'description';

    public const COL_STATUE = 'statue';

    public const COL_PLAN_ID = 'plan_id';

    use AuthenticatableTrait, HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'phone',
        'statue',
        'trial_ends_at',
        'plan_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function isManager()
    {
        return $this->role === ROLES::MANAGER->value;
    }

    public function isOwner()
    {
        return $this->role === ROLES::OWNER->value;
    }

    public function isSuperAdmin()
    {
        return $this->role === ROLES::SUPER_ADMIN->value;
    }

    /**
     * Check if the user has a specific role.
     *
     * @param  string  $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    // has many customers
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    // has many order
    public function orders()
    {
        return $this->hasMany(OrderSale::class);
    }

    // has many order purasher
    public function purchases()
    {
        return $this->hasMany(OrderPurchase::class);
    }

    // has many invoice
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // has many store
    public function stores()
    {
        // If user is owner, return stores where owner_id matches user id
        if ($this->isOwner()) {
            return $this->hasMany(Store::class, Store::COL_OWNER_ID);
        }

        // For other roles, return stores from UserStore pivot table
        return $this->belongsToMany(Store::class, UserStore::TABLE_NAME);
    }

    /**
     * Stores owned by this user (for owners only)
     * Use this for eager loading owner stores
     */
    public function ownedStores()
    {
        return $this->hasMany(Store::class, Store::COL_OWNER_ID);
    }

    // belongs to plan
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function workingStores()
    {
        return $this->belongsToMany(Store::class, UserStore::TABLE_NAME);
    }

    public function suppliers()
    {
        return $this->hasMany(Suppliers::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

 

    /**
     * Roles assigned to this user
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot('store_id')
            ->withTimestamps();
    }

 

    /**
     * Remove a role from the user
     *
     * @param  int|Role  $role  Role ID or Role model
     * @param  int|null  $storeId  Store ID for store-specific role
     */
    public function removeRole($role, ?int $storeId = null): void
    {
        $roleId = $role instanceof Role ? $role->id : $role;

        if ($storeId) {
            $this->roles()
                ->wherePivot('store_id', $storeId)
                ->detach($roleId);
        } else {
            $this->roles()->detach($roleId);
        }
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
