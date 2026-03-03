<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItems extends BaseModel
{
    use HasFactory;

    public const TABLE_NAME = 'invoice_items';

 
    public const COL_PRODUCT_ID = 'product_id';

    public const COL_INVOICE_ID = 'invoice_id';

    public const COL_LABEL = 'label';

    public const COL_QUANTITY = 'quantity';

    public const COL_PRICE = 'price';

    public const COL_INVOICE_PRICE = 'invoice_price';

    public const COL_DISCOUNT = 'discount';

    public const COL_TOTAL = 'total';

 
}
