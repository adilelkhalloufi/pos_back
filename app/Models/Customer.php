<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends BaseModel
{
    use HasFactory;

    protected $guarded = [];    // ********************* Settings

    public const TABLE_NAME = 'customers';


    public const COL_CIN = 'cin';

    public const COL_NAME = 'name';

    public const COL_ADRESS = 'adress';

    public const COL_PHONE = 'phone';

    public const COL_EMAIL = 'email';

    public const COL_BIRTHDAY = 'birthday';

    public const COL_GENDER = 'gender';

    public const COL_STATUS = 'status';

    public const COL_USER_ID = 'user_id';

    public const COL_STORE_ID = 'store_id';

    public const COL_TOTAL_ORDERS = 'total_orders';
    public const COL_TOTAL_PAYMENTS = 'total_payments';
    public const COL_TOTAL_PRESCRIPTIONS = 'total_prescriptions';
    public const COL_TOTAL_AMOUNT_ORDERS = 'total_amount_orders';
    public const COL_LAST_ORDER_DATE = 'last_order_date';




    public const STATUS_ARCHIVE = 'archive';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_BANKRUPT = 'bankrupt';

    public const STATUSES = [
        self::STATUS_ARCHIVE,
        self::STATUS_ACTIVE,
        self::STATUS_BANKRUPT,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(OrderSale::class);
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function payments()
    {
        return $this->hasMany(Payemnt::class);
    }



    // Method to calculate and update totals
    public function updateTotals()
    {
        $this->total_orders = $this->orders()->count();
        $this->total_prescriptions = $this->prescriptions()->count();

        // Assuming payments have 'amount' and 'status' (e.g., 'paid' or 'unpaid')
        $payments = $this->payments()->get();
        $this->total_amount_orders = $this->orders()->sum('total_amount');
        $this->total_payments = $payments->sum('amount');
        $this->save();
    }
}
