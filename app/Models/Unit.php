<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Unit extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'units';
    public const COL_ID = 'id';
    public const COL_NAME = 'name';
    public const COL_SYMBOL = 'symbol';
    public const COL_DESCRIPTION = 'description';
    public const COL_IS_ACTIVE = 'is_active';
    public const COL_STORE_ID = 'store_id';

    protected $fillable = [
        self::COL_NAME,
        self::COL_SYMBOL,
        self::COL_DESCRIPTION,
        self::COL_IS_ACTIVE,
        self::COL_STORE_ID,
    ];

    protected $casts = [
        self::COL_IS_ACTIVE => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
