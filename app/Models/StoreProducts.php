<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreProducts extends BaseModel
{
    protected $guarded = [];

    public const TABLE_NAME = 'store_products';

    public const COL_STORE_ID = 'store_id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_PRICE = 'price';
    public const COL_COST = 'cost';
    public const COL_STOCK = 'stock';



    public function store()
    {
        return $this->belongsTo(Store::class, self::COL_STORE_ID);
    }
    public function product()
    {
        return $this->belongsTo(Product::class, self::COL_PRODUCT_ID);
    }
}
