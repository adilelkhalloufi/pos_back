<?php

namespace App\Models;

class StorefrontOrder extends BaseModel
{
    public const TABLE_NAME = 'storefront_orders';

    public const COL_STORE_ID = 'store_id';
    public const COL_ORDER_NUMBER = 'order_number';
    public const COL_CUSTOMER_NAME = 'customer_name';
    public const COL_CUSTOMER_PHONE = 'customer_phone';
    public const COL_CUSTOMER_EMAIL = 'customer_email';
    public const COL_CUSTOMER_ADDRESS = 'customer_address';
    public const COL_NOTE = 'note';
    public const COL_STATUS = 'status';
    public const COL_SUBTOTAL = 'subtotal';
    public const COL_TOTAL = 'total';
    public const COL_CURRENCY = 'currency';
    public const COL_PLACED_AT = 'placed_at';

    protected $casts = [
        self::COL_SUBTOTAL => 'float',
        self::COL_TOTAL => 'float',
        self::COL_PLACED_AT => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, self::COL_STORE_ID);
    }

    public function items()
    {
        return $this->hasMany(StorefrontOrderItem::class, StorefrontOrderItem::COL_ORDER_ID);
    }
}
