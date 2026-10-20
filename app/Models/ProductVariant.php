<?php

namespace App\Models;

class ProductVariant extends BaseModel
{
    public const TABLE_NAME = 'product_variants';

    public const COL_PRODUCT_ID = 'product_id';
    public const COL_STORE_ID = 'store_id';
    public const COL_SKU = 'sku';
    public const COL_COLOR = 'color';
    public const COL_SIZE = 'size';
    public const COL_MATERIAL = 'material';
    public const COL_WEIGHT_GRAMS = 'weight_grams';
    public const COL_GEMSTONE = 'gemstone';
    public const COL_ATTRIBUTES = 'attributes';
    public const COL_PRICE = 'price';
    public const COL_STOCK = 'stock';
    public const COL_IS_ACTIVE = 'is_active';

    protected $casts = [
        self::COL_ATTRIBUTES => 'array',
        self::COL_PRICE => 'float',
        self::COL_STOCK => 'float',
        self::COL_WEIGHT_GRAMS => 'float',
        self::COL_IS_ACTIVE => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, self::COL_PRODUCT_ID);
    }

    public function store()
    {
        return $this->belongsTo(Store::class, self::COL_STORE_ID);
    }
}
