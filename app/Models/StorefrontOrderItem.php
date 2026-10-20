<?php

namespace App\Models;

class StorefrontOrderItem extends BaseModel
{
    public const TABLE_NAME = 'storefront_order_items';

    public const COL_ORDER_ID = 'order_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_VARIANT_ID = 'variant_id';
    public const COL_PRODUCT_NAME = 'product_name';
    public const COL_VARIANT_NAME = 'variant_name';
    public const COL_SKU = 'sku';
    public const COL_COLOR = 'color';
    public const COL_SIZE = 'size';
    public const COL_MATERIAL = 'material';
    public const COL_QUANTITY = 'quantity';
    public const COL_UNIT_PRICE = 'unit_price';
    public const COL_LINE_TOTAL = 'line_total';

    protected $casts = [
        self::COL_QUANTITY => 'float',
        self::COL_UNIT_PRICE => 'float',
        self::COL_LINE_TOTAL => 'float',
    ];

    public function order()
    {
        return $this->belongsTo(StorefrontOrder::class, self::COL_ORDER_ID);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, self::COL_PRODUCT_ID);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, self::COL_VARIANT_ID);
    }
}
