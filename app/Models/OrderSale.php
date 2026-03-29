<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderSale extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'order_sales';


    public const COL_ORDER_NUMBER = 'order_number';

    public const COL_CUSTOMER_ID = 'customer_id';

    public const COL_STATUS = 'status';

    public const COL_DISCOUNT = 'discount';

    public const COL_ADVANCE = 'advance';

    public const COL_IS_INVOICE = 'is_invoice';

    public const COL_TOTAL_COMMAND = 'total_command';

    public const COL_INVOICE_TOTAL = 'invoice_total';

    public const COL_TOTAL_PAYMENT = 'total_payment';

    public const COL_REST_A_PAY = 'rest_a_pay';

    public const COL_STORE_ID = 'store_id';

    public const COL_PAID_METHOD_ID = 'paid_method_id';

    public const COL_ASSURANCE_TYPE_ID = 'assurance_type_id';

    public const COL_USER_ID = 'user_id';

    public const COL_DELETED_AT = 'deleted_at';

    public const COL_CANCELLED_AT = 'cancelled_at';
    public const COL_CANCELLED_BY = 'cancelled_by';
    public const COL_CANCEL_REASON = 'cancel_reason';



    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments()
    {
        return $this->hasMany(Payemnt::class, 'order_id');
    }



    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, self::COL_CANCELLED_BY);
    }
}
