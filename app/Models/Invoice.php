<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
 
class Invoice extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'invoices';

 
    public const COL_USER_ID = 'user_id';

    public const COL_CUSTOMER_ID = 'customer_id';

    public const COL_PAID_METHOD_ID = 'paid_method_id';

    public const COL_TOTAL = 'total';

    public const COL_DISCOUNT = 'discount';

    public const COL_STATUS = 'status';

 

    // belong to customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // has many order item
    public function orderItems()
    {
        return $this->hasMany(OrderItems::class);
    }

    // belong to mode payement
    public function paymentMethod()
    {
        return $this->belongsTo(ModePayemnt::class);
    }
}
