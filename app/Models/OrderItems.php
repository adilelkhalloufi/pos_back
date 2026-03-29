<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItems extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'order_items';


    public const COL_PRODUCT_ID = 'product_id';

    public const COL_PRODUCT_TYPE = 'product_type';

    public const COL_ORDER_ID = 'order_id';

    public const COL_STORE_ID = 'store_id';

    public const COL_NAME = 'name';

    public const COL_QTE = 'qte';

    public const COL_PRICE = 'price';

    public const COL_INVOICE_PRICE = 'invoice_price';

    public const COL_DISCOUNT = 'discount';

    public const COL_TOTAL = 'total';

    public const COL_CATEGORY_ID = 'category_id';



    // belong to order
    public function order()
    {
        return $this->belongsTo(OrderSale::class);
    }

    public function product()
    {
        return $this->morphTo();
    }

    public function category()
    {
        return $this->belongsTo(Category::class, self::COL_CATEGORY_ID);
    }
}
