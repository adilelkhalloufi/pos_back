<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public const TABLE_NAME = 'roles';
    public const COL_NAME = 'name';
    public const COL_DISPLAY_NAME = 'display_name';
    public const COL_DESCRIPTION = 'description';
    public const COL_IS_SYSTEM = 'is_system';



    /**
     * Users that have this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role')
            ->withPivot('store_id')
            ->withTimestamps();
    }
}
