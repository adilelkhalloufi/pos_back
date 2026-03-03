<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
 
class Payemnt extends BaseModel
{

    public const TABLE_NAME = 'payemnts';

     public const COL_AMOUNT = 'amount';
    public const COL_STATUS = 'status';
    public const COL_STORE_ID = 'store_id';
    public const COL_NOTE = 'note';
    public const COL_ORDER_ID = 'order_id';
    public const COL_ORDER_PURCHASE_ID = 'order_purchase_id';
    public const COL_CUSTOMER_ID = 'customer_id';
    public const COL_MODE_PAYEMNT_ID = 'mode_payemnt_id';
    public const COL_USER_ID = 'user_id';
 

    use HasFactory;

    protected $guarded = [];
    // belong to customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // belong to mode de payement
    public function mode_payemnt()
    {
        return $this->belongsTo(ModePayemnt::class);
    }

    // belong to order
    public function order_sale()
    {
        return $this->belongsTo(OrderSale::class, self::COL_ORDER_ID, OrderSale::COL_ID);
    }

    // belong to order
    public function order_purchase()
    {
        return $this->belongsTo(OrderPurchase::class, self::COL_ORDER_ID, OrderPurchase::COL_ID);
    }

    // belong to order
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, self::COL_ORDER_ID, Invoice::COL_ID);
    }
}
