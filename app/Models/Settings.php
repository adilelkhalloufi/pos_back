<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Settings extends BaseModel
{

    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'settings';

    public const COL_LICENCE = 'license_key';
    public const COL_EXPIRED = 'is_license_expired';
    public const COL_EXPIRED_DATE = 'license_expiry_date';
    public const COL_INVOICE_NUMBER = 'invoice_sequence';
    public const COL_ORDER_PURCHASE_NUMBER = 'purchase_sequence';
    public const COL_ORDER_SALE_NUMBER = 'sale_sequence';
    public const COL_PREFIX_ORDER = 'order_prefix';
    public const COL_PREFIX_INVOICE = 'invoice_prefix';
    public const COL_PREFIX_PURCHASE = 'purchase_prefix';
    public const COL_HEADER = 'document_header';
    public const COL_FOOTER = 'document_footer';
    public const COL_COMPANY_NAME = 'company_name';
    public const COL_USER_ID = 'user_id';
    public const COL_STORE_ID = 'store_id';


  
}
