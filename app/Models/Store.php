<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'stores';


    public const COL_NAME = 'name';

    public const COL_EMAIL = 'email';

    public const COL_PHONE = 'phone';

    public const COL_ADDRESS = 'address';

    public const COL_LOGO = 'logo';

    public const COL_WEBSITE = 'website';

    public const COL_ZIP_CODE = 'zip_code';

    public const COL_CITY = 'city';

    public const COL_IF = 'if';

    public const COL_ICE = 'ice';

    public const COL_RC = 'rc';

    public const COL_PATENTE = 'patente';

    public const COL_CNSS = 'cnss';

    public const COL_TAX = 'tax';

    public const COL_LATITUDE = 'latitude';

    public const COL_LONGITUDE = 'longitude';

    public const COL_OWNER_ID = 'owner_id';



    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function workers()
    {
        return $this->belongsToMany(User::class, 'user_stores', 'store_id', 'user_id');
    }

    public function cites()
    {
        return $this->belongsTo(City::class, 'city', 'id');
    }

    public function products()
    {
        return $this->hasMany(StoreProducts::class, 'store_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'store_id');
    }

    public function sales()
    {
        return $this->hasMany(OrderSale::class, 'store_id');
    }

    public function purchases()
    {
        return $this->hasMany(OrderPurchase::class, 'store_id');
    }

    public function suppliers()
    {
        return $this->hasMany(Suppliers::class, 'store_id');
    }

    public function typeglass()
    {
        return $this->hasMany(TypeGlasses::class, 'store_id');
    }
    public function brands()
    {
        return $this->hasMany(Brands::class, 'store_id');
    }

    public function payments()
    {
        return $this->hasMany(Payemnt::class, 'store_id');
    }
}
