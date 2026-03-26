<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Settings extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public const TABLE_NAME = 'settings';

    // Setting Keys - stored as records in database
    // License
    public const KEY_LICENCE = 'license_key';
    public const KEY_EXPIRED = 'is_license_expired';
    public const KEY_EXPIRED_DATE = 'license_expiry_date';

    // Sequences
    public const KEY_INVOICE_NUMBER = 'invoice_sequence';
    public const KEY_ORDER_PURCHASE_NUMBER = 'purchase_sequence';
    public const KEY_ORDER_SALE_NUMBER = 'sale_sequence';
    public const KEY_QUOTE_NUMBER = 'quote_sequence';

    // Prefixes
    public const KEY_PREFIX_ORDER = 'order_prefix';
    public const KEY_PREFIX_INVOICE = 'invoice_prefix';
    public const KEY_PREFIX_PURCHASE = 'purchase_prefix';
    public const KEY_PREFIX_QUOTE = 'quote_prefix';

    // Company info
    public const KEY_COMPANY_NAME = 'company_name';
    public const KEY_COMPANY_ADDRESS = 'company_address';
    public const KEY_COMPANY_PHONE = 'company_phone';
    public const KEY_COMPANY_EMAIL = 'company_email';
    public const KEY_COMPANY_TAX_NUMBER = 'company_tax_number';
    public const KEY_COMPANY_LOGO = 'company_logo';

    // Document settings
    public const KEY_HEADER = 'document_header';
    public const KEY_FOOTER = 'document_footer';
    public const KEY_SHOW_PAYMENT_DETAILS = 'show_payment_details_on_invoice';

    // Printing
    public const KEY_MAX_PRINT_COPIES = 'max_print_copies';
    public const KEY_AUTO_PRINT_ORDER = 'auto_print_order';
    public const KEY_AUTO_PRINT_INVOICE = 'auto_print_invoice';
    public const KEY_PRINT_AFTER_PAYMENT = 'print_after_payment';

    // Secondary display (afficheur)
    public const KEY_SECONDARY_DISPLAY_ENABLED = 'secondary_display_enabled';
    public const KEY_SECONDARY_DISPLAY_CONNECTION = 'secondary_display_connection';
    public const KEY_SECONDARY_DISPLAY_COM_PORT = 'secondary_display_com_port';
    public const KEY_SECONDARY_DISPLAY_X = 'secondary_display_x';
    public const KEY_SECONDARY_DISPLAY_Y = 'secondary_display_y';
    public const KEY_SECONDARY_DISPLAY_WIDTH = 'secondary_display_width';
    public const KEY_SECONDARY_DISPLAY_HEIGHT = 'secondary_display_height';

    // Passport/Card reader
    public const KEY_PASSPORT_READER_ENABLED = 'passport_reader_enabled';
    public const KEY_PASSPORT_READER_COM_PORT = 'passport_reader_com_port';
    public const KEY_PASSPORT_READER_BAUD_RATE = 'passport_reader_baud_rate';
    public const KEY_PASSPORT_READER_PROVIDER = 'passport_reader_provider';

    // POS settings
    public const KEY_CURRENCY = 'currency';
    public const KEY_CURRENCY_SYMBOL = 'currency_symbol';
    public const KEY_TAX_RATE = 'tax_rate';
    public const KEY_TAX_INCLUSIVE = 'tax_inclusive';
    public const KEY_LOW_STOCK_ALERT = 'low_stock_alert_threshold';
    public const KEY_ENABLE_BARCODE_SCANNER = 'enable_barcode_scanner';
    public const KEY_ALLOW_DECIMAL_QUANTITIES = 'allow_decimal_quantities';
    public const KEY_CASH_DRAWER_ENABLED = 'cash_drawer_enabled';

    // Quote/Devis settings
    public const KEY_QUOTE_VALIDITY_DAYS = 'quote_validity_days';
    public const KEY_QUOTE_TO_INVOICE_AUTO = 'quote_to_invoice_auto_convert';

    // Backward compatibility (deprecated - use KEY_ constants)
    public const COL_LICENCE = self::KEY_LICENCE;
    public const COL_EXPIRED = self::KEY_EXPIRED;
    public const COL_EXPIRED_DATE = self::KEY_EXPIRED_DATE;
    public const COL_INVOICE_NUMBER = self::KEY_INVOICE_NUMBER;
    public const COL_ORDER_PURCHASE_NUMBER = self::KEY_ORDER_PURCHASE_NUMBER;
    public const COL_ORDER_SALE_NUMBER = self::KEY_ORDER_SALE_NUMBER;
    public const COL_PREFIX_ORDER = self::KEY_PREFIX_ORDER;
    public const COL_PREFIX_INVOICE = self::KEY_PREFIX_INVOICE;
    public const COL_PREFIX_PURCHASE = self::KEY_PREFIX_PURCHASE;
    public const COL_HEADER = self::KEY_HEADER;
    public const COL_FOOTER = self::KEY_FOOTER;
    public const COL_COMPANY_NAME = self::KEY_COMPANY_NAME;
    public const COL_MAX_PRINT_COPIES = self::KEY_MAX_PRINT_COPIES;
    public const COL_SECONDARY_DISPLAY_ENABLED = self::KEY_SECONDARY_DISPLAY_ENABLED;
    public const COL_SECONDARY_DISPLAY_CONNECTION = self::KEY_SECONDARY_DISPLAY_CONNECTION;
    public const COL_SECONDARY_DISPLAY_COM_PORT = self::KEY_SECONDARY_DISPLAY_COM_PORT;
    public const COL_SECONDARY_DISPLAY_X = self::KEY_SECONDARY_DISPLAY_X;
    public const COL_SECONDARY_DISPLAY_Y = self::KEY_SECONDARY_DISPLAY_Y;
    public const COL_SECONDARY_DISPLAY_WIDTH = self::KEY_SECONDARY_DISPLAY_WIDTH;
    public const COL_SECONDARY_DISPLAY_HEIGHT = self::KEY_SECONDARY_DISPLAY_HEIGHT;
    public const COL_PASSPORT_READER_ENABLED = self::KEY_PASSPORT_READER_ENABLED;
    public const COL_PASSPORT_READER_COM_PORT = self::KEY_PASSPORT_READER_COM_PORT;
    public const COL_PASSPORT_READER_BAUD_RATE = self::KEY_PASSPORT_READER_BAUD_RATE;
    public const COL_PASSPORT_READER_PROVIDER = self::KEY_PASSPORT_READER_PROVIDER;
    public const COL_STORE_ID = 'store_id';

    /**
     * Get a setting value for a specific store
     */
    public static function get(string $key, int $storeId, mixed $default = null): mixed
    {
        $cacheKey = "settings.{$storeId}.{$key}";

        return Cache::remember($cacheKey, 3600, function () use ($key, $storeId, $default) {
            $setting = self::where('key', $key)
                ->where('store_id', $storeId)
                ->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    /**
     * Set a setting value for a specific store
     */
    public static function set(string $key, mixed $value, int $storeId, ?string $type = null, ?string $description = null): self
    {
        $type = $type ?? self::inferType($value);
        $cacheKey = "settings.{$storeId}.{$key}";
        Cache::forget($cacheKey);

        return self::updateOrCreate(
            [
                'key' => $key,
                'store_id' => $storeId,
            ],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'description' => $description,
            ]
        );
    }

    /**
     * Get all settings for a store as key-value array
     */
    public static function getAllForStore(int $storeId): array
    {
        $settings = self::where('store_id', $storeId)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = self::castValue($setting->value, $setting->type);
        }

        return $result;
    }

    /**
     * Cast value based on type
     */
    private static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'float', 'double' => (float) $value,
            'json', 'array' => json_decode($value, true),
            default => $value,
        };
    }

    /**
     * Infer type from value
     */
    private static function inferType(mixed $value): string
    {
        return match (true) {
            is_bool($value) => 'boolean',
            is_int($value) => 'integer',
            is_float($value) => 'float',
            is_array($value) => 'json',
            default => 'string',
        };
    }

    /**
     * Clear cache for store
     */
    public static function clearCache(int $storeId): void
    {
        $keys = self::where('store_id', $storeId)->pluck('key');
        foreach ($keys as $key) {
            Cache::forget("settings.{$storeId}.{$key}");
        }
    }
}
