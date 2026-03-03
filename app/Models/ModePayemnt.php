<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
 
class ModePayemnt extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'mode_payemnts';

 
    public const COL_NAME = 'name';

    public const COL_CODE = 'code';

    public const COL_DESCRIPTION = 'description';

    public const COL_ICON = 'icon';

    public const COL_USER_ID = 'user_id';

 

    // has many invoive
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    // has many order
    public function orders()
    {
        return $this->hasMany(OrderSale::class);
    }

    // has many order prusher
    public function orderPushers()
    {
        return $this->hasMany(OrderPurchase::class);
    }
}
