<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
 
class OrderPurchaseItems extends BaseModel
{ 

   public const TABLE_NAME = 'order_purchase_items';

    public const COL_ID = 'id';
    public const COL_PRODUCT_ID = 'product_id';
    public const COL_ORDER_ID = 'order_id';
    public const COL_STORE_ID = 'store_id';
    public const COL_NAME = 'name';
    public const COL_QUANTITY = 'quantity';
    public const COL_PRICE = 'price';
    public const COL_TOTAL = 'total';
 

    use HasFactory;

    protected $guarded = [];
   
   public function order()
    {
        return $this->belongsTo(OrderPurchase::class, self::COL_ORDER_ID, OrderPurchase::COL_ID);
    }

    public function product()
    {
        return $this->belongsTo(Product::class,self::COL_PRODUCT_ID, Product::COL_ID);
    }
   }
