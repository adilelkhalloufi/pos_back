<?php

namespace Database\Seeders;

use App\Models\Settings;
use App\Models\Store;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all stores to seed settings for each
        $stores = Store::all();

        foreach ($stores as $store) {
            $this->seedStoreSettings($store->id);
        }
    }

    /**
     * Seed default settings for a specific store
     */
    private function seedStoreSettings(int $storeId): void
    {
        $settings = [
            // Sequences
            [
                'key' => Settings::KEY_INVOICE_NUMBER,
                'value' => '0',
                'type' => 'integer',
                'description' => 'Current invoice sequence number',
            ],
            [
                'key' => Settings::KEY_ORDER_SALE_NUMBER,
                'value' => '0',
                'type' => 'integer',
                'description' => 'Current sale order sequence number',
            ],
            [
                'key' => Settings::KEY_ORDER_PURCHASE_NUMBER,
                'value' => '0',
                'type' => 'integer',
                'description' => 'Current purchase order sequence number',
            ],
            [
                'key' => Settings::KEY_QUOTE_NUMBER,
                'value' => '0',
                'type' => 'integer',
                'description' => 'Current quote/devis sequence number',
            ],

            // Prefixes
            [
                'key' => Settings::KEY_PREFIX_INVOICE,
                'value' => 'INV-',
                'type' => 'string',
                'description' => 'Prefix for invoice numbers',
            ],
            [
                'key' => Settings::KEY_PREFIX_ORDER,
                'value' => 'ORD-',
                'type' => 'string',
                'description' => 'Prefix for sale order numbers',
            ],
            [
                'key' => Settings::KEY_PREFIX_PURCHASE,
                'value' => 'PUR-',
                'type' => 'string',
                'description' => 'Prefix for purchase order numbers',
            ],
            [
                'key' => Settings::KEY_PREFIX_QUOTE,
                'value' => 'QUO-',
                'type' => 'string',
                'description' => 'Prefix for quote/devis numbers',
            ],

            // Company Information
            [
                'key' => Settings::KEY_COMPANY_NAME,
                'value' => '',
                'type' => 'string',
                'description' => 'Company name displayed on documents',
            ],
            [
                'key' => Settings::KEY_COMPANY_ADDRESS,
                'value' => '',
                'type' => 'string',
                'description' => 'Company address',
            ],
            [
                'key' => Settings::KEY_COMPANY_PHONE,
                'value' => '',
                'type' => 'string',
                'description' => 'Company phone number',
            ],
            [
                'key' => Settings::KEY_COMPANY_EMAIL,
                'value' => '',
                'type' => 'string',
                'description' => 'Company email address',
            ],
            [
                'key' => Settings::KEY_COMPANY_TAX_NUMBER,
                'value' => '',
                'type' => 'string',
                'description' => 'Company tax/VAT number',
            ],
            [
                'key' => Settings::KEY_COMPANY_LOGO,
                'value' => '',
                'type' => 'string',
                'description' => 'Path to company logo',
            ],

            // Document Settings
            [
                'key' => Settings::KEY_HEADER,
                'value' => '',
                'type' => 'string',
                'description' => 'Document header text',
            ],
            [
                'key' => Settings::KEY_FOOTER,
                'value' => '',
                'type' => 'string',
                'description' => 'Document footer text',
            ],
            [
                'key' => Settings::KEY_SHOW_PAYMENT_DETAILS,
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Show payment details on invoice',
            ],

            // Printing Settings
            [
                'key' => Settings::KEY_MAX_PRINT_COPIES,
                'value' => '1',
                'type' => 'integer',
                'description' => 'Maximum number of print copies for orders/invoices',
            ],
            [
                'key' => Settings::KEY_AUTO_PRINT_ORDER,
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically print orders when created',
            ],
            [
                'key' => Settings::KEY_AUTO_PRINT_INVOICE,
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically print invoices when created',
            ],
            [
                'key' => Settings::KEY_PRINT_AFTER_PAYMENT,
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Print invoice after payment is completed',
            ],

            // Secondary Display Settings (Afficheur)
            [
                'key' => Settings::KEY_SECONDARY_DISPLAY_ENABLED,
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable secondary display for customer',
            ],
            [
                'key' => Settings::KEY_SECONDARY_DISPLAY_CONNECTION,
                'value' => 'serial',
                'type' => 'string',
                'description' => 'Connection type for secondary display (serial, usb, network)',
            ],
            [
                'key' => Settings::KEY_SECONDARY_DISPLAY_COM_PORT,
                'value' => 'COM1',
                'type' => 'string',
                'description' => 'COM port for secondary display',
            ],
            [
                'key' => Settings::KEY_SECONDARY_DISPLAY_X,
                'value' => '0',
                'type' => 'integer',
                'description' => 'X position of secondary display window',
            ],
            [
                'key' => Settings::KEY_SECONDARY_DISPLAY_Y,
                'value' => '0',
                'type' => 'integer',
                'description' => 'Y position of secondary display window',
            ],
            [
                'key' => Settings::KEY_SECONDARY_DISPLAY_WIDTH,
                'value' => '800',
                'type' => 'integer',
                'description' => 'Width of secondary display window',
            ],
            [
                'key' => Settings::KEY_SECONDARY_DISPLAY_HEIGHT,
                'value' => '600',
                'type' => 'integer',
                'description' => 'Height of secondary display window',
            ],

            // Passport/Card Reader Settings
            [
                'key' => Settings::KEY_PASSPORT_READER_ENABLED,
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable passport/ID card reader',
            ],
            [
                'key' => Settings::KEY_PASSPORT_READER_COM_PORT,
                'value' => 'COM2',
                'type' => 'string',
                'description' => 'COM port for passport reader',
            ],
            [
                'key' => Settings::KEY_PASSPORT_READER_BAUD_RATE,
                'value' => '9600',
                'type' => 'integer',
                'description' => 'Baud rate for passport reader',
            ],
            [
                'key' => Settings::KEY_PASSPORT_READER_PROVIDER,
                'value' => 'default',
                'type' => 'string',
                'description' => 'Passport reader provider/driver',
            ],

            // Currency & Tax Settings
            [
                'key' => Settings::KEY_CURRENCY,
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Default currency code (USD, EUR, MAD, etc.)',
            ],
            [
                'key' => Settings::KEY_CURRENCY_SYMBOL,
                'value' => '$',
                'type' => 'string',
                'description' => 'Currency symbol to display',
            ],
            [
                'key' => Settings::KEY_TAX_RATE,
                'value' => '0',
                'type' => 'float',
                'description' => 'Default tax rate percentage',
            ],
            [
                'key' => Settings::KEY_TAX_INCLUSIVE,
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Prices include tax by default',
            ],

            // POS Settings
            [
                'key' => Settings::KEY_LOW_STOCK_ALERT,
                'value' => '10',
                'type' => 'integer',
                'description' => 'Low stock alert threshold',
            ],
            [
                'key' => Settings::KEY_ENABLE_BARCODE_SCANNER,
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable barcode scanner support',
            ],
            [
                'key' => Settings::KEY_ALLOW_DECIMAL_QUANTITIES,
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Allow decimal quantities for products',
            ],
            [
                'key' => Settings::KEY_CASH_DRAWER_ENABLED,
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable cash drawer integration',
            ],

            // Quote/Devis Settings
            [
                'key' => Settings::KEY_QUOTE_VALIDITY_DAYS,
                'value' => '30',
                'type' => 'integer',
                'description' => 'Default validity period for quotes in days',
            ],
            [
                'key' => Settings::KEY_QUOTE_TO_INVOICE_AUTO,
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically convert quote to invoice when accepted',
            ],
        ];

        foreach ($settings as $setting) {
            Settings::updateOrCreate(
                [
                    'key' => $setting['key'],
                    'store_id' => $storeId,
                ],
                [
                    'value' => $setting['value'],
                    'type' => $setting['type'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
}
