<?php

namespace App\Models;

class StorefrontProduct extends BaseModel
{
    public const TABLE_NAME = 'storefront_products';

    public const COL_STORE_ID = 'store_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_IS_PUBLISHED = 'is_published';
    public const COL_SORT_ORDER = 'sort_order';
    public const COL_SHORT_DESCRIPTION = 'short_description';
    public const COL_COVER_IMAGE = 'cover_image';

    protected $casts = [
        self::COL_IS_PUBLISHED => 'boolean',
        self::COL_SORT_ORDER => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, self::COL_STORE_ID);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, self::COL_PRODUCT_ID);
    }
}
