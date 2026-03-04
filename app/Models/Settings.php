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

    // Printing
    public const COL_MAX_PRINT_COPIES = 'max_print_copies';

    // Secondary display
    public const COL_SECONDARY_DISPLAY_ENABLED = 'secondary_display_enabled';
    public const COL_SECONDARY_DISPLAY_CONNECTION = 'secondary_display_connection';
    public const COL_SECONDARY_DISPLAY_COM_PORT = 'secondary_display_com_port';
    public const COL_SECONDARY_DISPLAY_X = 'secondary_display_x';
    public const COL_SECONDARY_DISPLAY_Y = 'secondary_display_y';
    public const COL_SECONDARY_DISPLAY_WIDTH = 'secondary_display_width';
    public const COL_SECONDARY_DISPLAY_HEIGHT = 'secondary_display_height';

    // Passport reader
    public const COL_PASSPORT_READER_ENABLED = 'passport_reader_enabled';
    public const COL_PASSPORT_READER_COM_PORT = 'passport_reader_com_port';
    public const COL_PASSPORT_READER_BAUD_RATE = 'passport_reader_baud_rate';
    public const COL_PASSPORT_READER_PROVIDER = 'passport_reader_provider';

    protected $casts = [
        self::COL_SECONDARY_DISPLAY_ENABLED => 'boolean',
        self::COL_PASSPORT_READER_ENABLED => 'boolean',
        self::COL_MAX_PRINT_COPIES => 'integer',
        self::COL_SECONDARY_DISPLAY_X => 'integer',
        self::COL_SECONDARY_DISPLAY_Y => 'integer',
        self::COL_SECONDARY_DISPLAY_WIDTH => 'integer',
        self::COL_SECONDARY_DISPLAY_HEIGHT => 'integer',
        self::COL_PASSPORT_READER_BAUD_RATE => 'integer',
    ];
}
