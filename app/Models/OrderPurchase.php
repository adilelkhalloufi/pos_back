<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderPurchase extends BaseModel
{

    public const TABLE_NAME = 'order_purchases';

    public const COL_ORDER_NUMBER = 'order_number';
    public const COL_REFERENCE = 'reference';
    public const COL_PAYMENT_TERM = 'payment_term';
    public const COL_PAID_METHOD_ID = 'paid_method_id';
    public const COL_DUE_DATE = 'due_date';
    public const COL_STATUS = 'status';
    public const COL_DELIVERY_STATUS = 'delivery_status';
    public const COL_ORDERED_DATE = 'ordered_date';
    public const COL_EXPECTED_DELIVERY_DATE = 'expected_delivery_date';
    public const COL_FIRST_DELIVERY_DATE = 'first_delivery_date';
    public const COL_LAST_DELIVERY_DATE = 'last_delivery_date';
    public const COL_STORE_ID = 'store_id';
    public const COL_SUPPLIER_ID = 'supplier_id';
    public const COL_USER_ID = 'user_id';
    public const COL_PUBLIC_NOTE = 'public_note';
    public const COL_PRIVATE_NOTE = 'private_note';
    public const COL_DISCOUNT = 'discount';
    public const COL_TOTAL_HT = 'total_ht';
    public const COL_TOTAL_TVA = 'total_tva';
    public const COL_TOTAL_TTC = 'total_ttc';

    use HasFactory;

    protected $guarded = [];
    // has many order items
    public function orderItems()
    {
        return $this->hasMany(OrderPurchaseItems::class, 'order_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Suppliers::class);
    }

    public function payments()
    {
        return $this->hasMany(Payemnt::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get all deliveries for this purchase order
     */
    public function deliveries()
    {
        return $this->hasMany(PurchaseDelivery::class, 'order_purchase_id');
    }
}
