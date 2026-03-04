<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductComponent extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'product_components';
    public const COL_ID = 'id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_COMPONENT_ID = 'component_id';
    public const COL_QUANTITY = 'quantity';
    public const COL_UNIT_ID = 'unit_id';
    public const COL_NOTE = 'note';

    protected $fillable = [
        self::COL_PRODUCT_ID,
        self::COL_COMPONENT_ID,
        self::COL_QUANTITY,
        self::COL_UNIT_ID,
        self::COL_NOTE,
    ];

    protected $casts = [
        self::COL_QUANTITY => 'float',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, self::COL_PRODUCT_ID);
    }

    public function component()
    {
        return $this->belongsTo(Product::class, self::COL_COMPONENT_ID);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
