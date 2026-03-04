<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PriceChangeLog extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'price_change_logs';
    public const COL_ID = 'id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_USER_ID = 'user_id';
    public const COL_FIELD = 'field';
    public const COL_OLD_VALUE = 'old_value';
    public const COL_NEW_VALUE = 'new_value';
    public const COL_EFFECTIVE_DATE = 'effective_date';
    public const COL_REASON = 'reason';
    public const COL_STORE_ID = 'store_id';

    protected $fillable = [
        self::COL_PRODUCT_ID,
        self::COL_USER_ID,
        self::COL_FIELD,
        self::COL_OLD_VALUE,
        self::COL_NEW_VALUE,
        self::COL_EFFECTIVE_DATE,
        self::COL_REASON,
        self::COL_STORE_ID,
    ];

    protected $casts = [
        self::COL_OLD_VALUE => 'float',
        self::COL_NEW_VALUE => 'float',
        self::COL_EFFECTIVE_DATE => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
