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
    public const COL_STORE_ID = 'store_id';
    public const COL_SUPPLIER_ID = 'supplier_id';
    public const COL_USER_ID = 'user_id';
    public const COL_PUBLIC_NOTE = 'public_note';
    public const COL_PRIVATE_NOTE = 'private_note';
    public const COL_DISCOUNT = 'discount';
 
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
}
