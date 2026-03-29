<?php

namespace Database\Seeders;

use App\Models\Setting;
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
                'key' => 'invoice_number',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Current invoice sequence number',
            ],
            [
                'key' => 'order_sale_number',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Current sale order sequence number',
            ],
            [
                'key' => 'order_purchase_number',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Current purchase order sequence number',
            ],
            [
                'key' => 'quote_number',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Current quote/devis sequence number',
            ],

            // Prefixes
            [
                'key' => 'prefix_invoice',
                'value' => 'INV-',
                'type' => 'string',
                'description' => 'Prefix for invoice numbers',
            ],
            [
                'key' => 'prefix_order',
                'value' => 'ORD-',
                'type' => 'string',
                'description' => 'Prefix for sale order numbers',
            ],
            [
                'key' => 'prefix_purchase',
                'value' => 'PUR-',
                'type' => 'string',
                'description' => 'Prefix for purchase order numbers',
            ],
            [
                'key' => 'prefix_quote',
                'value' => 'QUO-',
                'type' => 'string',
                'description' => 'Prefix for quote/devis numbers',
            ],

            // Company Information
            [
                'key' => 'company_name',
                'value' => 'DETROIT SEA FOOD',
                'type' => 'string',
                'description' => 'Company name displayed on documents',
            ],
            [
                'key' => 'company_address',
                'value' => '',
                'type' => 'string',
                'description' => 'Company address',
            ],
            [
                'key' => 'company_phone',
                'value' => '',
                'type' => 'string',
                'description' => 'Company phone number',
            ],
            [
                'key' => 'company_email',
                'value' => '',
                'type' => 'string',
                'description' => 'Company email address',
            ],
            [
                'key' => 'company_tax_number',
                'value' => '',
                'type' => 'string',
                'description' => 'Company tax/VAT number',
            ],
            [
                'key' => 'company_logo',
                'value' => '',
                'type' => 'string',
                'description' => 'Path to company logo',
            ],

            // Document Settings
            [
                'key' => 'header',
                'value' => '',
                'type' => 'string',
                'description' => 'Document header text',
            ],
            [
                'key' => 'footer',
                'value' => 'Tél : - FAX:
ICE : 002175372000008 - IF: 31837969 - RC: 93153',
                'type' => 'string',
                'description' => 'Document footer text',
            ],
            [
                'key' => 'show_payment_details',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Show payment details on invoice',
            ],

            // Printing Settings
            [
                'key' => 'max_print_copies',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Maximum number of print copies for orders/invoices',
            ],
            [
                'key' => 'auto_print_order',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically print orders when created',
            ],
            [
                'key' => 'auto_print_invoice',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically print invoices when created',
            ],
            [
                'key' => 'print_after_payment',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Print invoice after payment is completed',
            ],

            // Secondary Display Settings (Afficheur)
            [
                'key' => 'secondary_display_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable secondary display for customer',
            ],
            [
                'key' => 'secondary_display_connection',
                'value' => 'serial',
                'type' => 'string',
                'description' => 'Connection type for secondary display (serial, usb, network)',
            ],
            [
                'key' => 'secondary_display_com_port',
                'value' => 'COM1',
                'type' => 'string',
                'description' => 'COM port for secondary display',
            ],
            [
                'key' => 'secondary_display_x',
                'value' => '0',
                'type' => 'integer',
                'description' => 'X position of secondary display window',
            ],
            [
                'key' => 'secondary_display_y',
                'value' => '0',
                'type' => 'integer',
                'description' => 'Y position of secondary display window',
            ],
            [
                'key' => 'secondary_display_width',
                'value' => '800',
                'type' => 'integer',
                'description' => 'Width of secondary display window',
            ],
            [
                'key' => 'secondary_display_height',
                'value' => '600',
                'type' => 'integer',
                'description' => 'Height of secondary display window',
            ],

            // Passport/Card Reader Settings
            [
                'key' => 'passport_reader_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable passport/ID card reader',
            ],
            [
                'key' => 'passport_reader_com_port',
                'value' => 'COM2',
                'type' => 'string',
                'description' => 'COM port for passport reader',
            ],
            [
                'key' => 'passport_reader_baud_rate',
                'value' => '9600',
                'type' => 'integer',
                'description' => 'Baud rate for passport reader',
            ],
            [
                'key' => 'passport_reader_provider',
                'value' => 'default',
                'type' => 'string',
                'description' => 'Passport reader provider/driver',
            ],

            // Currency & Tax Settings
            [
                'key' => 'currency',
                'value' => 'USD',
                'type' => 'string',
                'description' => 'Default currency code (USD, EUR, MAD, etc.)',
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'string',
                'description' => 'Currency symbol to display',
            ],
            [
                'key' => 'tax_rate',
                'value' => '0',
                'type' => 'float',
                'description' => 'Default tax rate percentage',
            ],
            [
                'key' => 'tax_inclusive',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Prices include tax by default',
            ],

            // POS Settings
            [
                'key' => 'low_stock_alert',
                'value' => '10',
                'type' => 'integer',
                'description' => 'Low stock alert threshold',
            ],
            [
                'key' => 'enable_barcode_scanner',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Enable barcode scanner support',
            ],
            [
                'key' => 'allow_decimal_quantities',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Allow decimal quantities for products',
            ],
            [
                'key' => 'cash_drawer_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Enable cash drawer integration',
            ],

            // Quote/Devis Settings
            [
                'key' => 'quote_validity_days',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Default validity period for quotes in days',
            ],
            [
                'key' => 'quote_to_invoice_auto',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Automatically convert quote to invoice when accepted',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
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
