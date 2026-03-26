<?php

namespace App\Services\Setting;

use App\Models\Settings;

class SettingService
{
    public function __construct() {}

    /**
     * Get a specific setting value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return Settings::get($key, currentStoreId(), $default);
    }

    /**
     * Set a specific setting value
     */
    public function set(string $key, mixed $value, ?string $description = null): Settings
    {
        return Settings::set($key, $value, currentStoreId(), null, $description);
    }

    /**
     * Get all settings for current store as array
     */
    public function getAllSettings(): array
    {
        return Settings::getAllForStore(currentStoreId());
    }

    // Sale Order Number methods
    public function getNextSaleOrderNumber(): string
    {
        $nextNumber = $this->get(Settings::KEY_ORDER_SALE_NUMBER, 0) + 1;
        $prefix = $this->get(Settings::KEY_PREFIX_ORDER, 'ORD-');
        return $prefix . str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function incrementSaleOrderNumber(): void
    {
        $currentNumber = $this->get(Settings::KEY_ORDER_SALE_NUMBER, 0);
        $this->set(Settings::KEY_ORDER_SALE_NUMBER, $currentNumber + 1);
    }

    // Sale Invoice Number methods
    public function getNextSaleInvoiceNumber(): string
    {
        $nextNumber = $this->get(Settings::KEY_INVOICE_NUMBER, 0) + 1;
        $prefix = $this->get(Settings::KEY_PREFIX_INVOICE, 'INV-');
        return $prefix . str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function incrementSaleInvoiceNumber(): void
    {
        $currentNumber = $this->get(Settings::KEY_INVOICE_NUMBER, 0);
        $this->set(Settings::KEY_INVOICE_NUMBER, $currentNumber + 1);
    }

    // Purchase Order Number methods
    public function getNextPurchaseOrderNumber(): string
    {
        $nextNumber = $this->get(Settings::KEY_ORDER_PURCHASE_NUMBER, 0) + 1;
        $prefix = $this->get(Settings::KEY_PREFIX_PURCHASE, 'PUR-');
        return $prefix . str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function incrementPurchaseOrderNumber(): void
    {
        $currentNumber = $this->get(Settings::KEY_ORDER_PURCHASE_NUMBER, 0);
        $this->set(Settings::KEY_ORDER_PURCHASE_NUMBER, $currentNumber + 1);
    }

    // Quote Number methods
    public function getNextQuoteNumber(): string
    {
        $nextNumber = $this->get(Settings::KEY_QUOTE_NUMBER, 0) + 1;
        $prefix = $this->get(Settings::KEY_PREFIX_QUOTE, 'QUO-');
        return $prefix . str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function incrementQuoteNumber(): void
    {
        $currentNumber = $this->get(Settings::KEY_QUOTE_NUMBER, 0);
        $this->set(Settings::KEY_QUOTE_NUMBER, $currentNumber + 1);
    }

    /**
     * Update multiple settings at once
     */
    public function updateSettings(array $attributes): bool
    {
        $allowed = [
            Settings::KEY_COMPANY_NAME,
            Settings::KEY_COMPANY_ADDRESS,
            Settings::KEY_COMPANY_PHONE,
            Settings::KEY_COMPANY_EMAIL,
            Settings::KEY_COMPANY_TAX_NUMBER,
            Settings::KEY_COMPANY_LOGO,
            Settings::KEY_CURRENCY,
            Settings::KEY_CURRENCY_SYMBOL,
            Settings::KEY_TAX_RATE,
            Settings::KEY_TAX_INCLUSIVE,
            Settings::KEY_HEADER,
            Settings::KEY_FOOTER,
            Settings::KEY_SHOW_PAYMENT_DETAILS,
            Settings::KEY_PREFIX_ORDER,
            Settings::KEY_PREFIX_INVOICE,
            Settings::KEY_PREFIX_PURCHASE,
            Settings::KEY_PREFIX_QUOTE,
            Settings::KEY_MAX_PRINT_COPIES,
            Settings::KEY_AUTO_PRINT_ORDER,
            Settings::KEY_AUTO_PRINT_INVOICE,
            Settings::KEY_PRINT_AFTER_PAYMENT,
            Settings::KEY_SECONDARY_DISPLAY_ENABLED,
            Settings::KEY_SECONDARY_DISPLAY_CONNECTION,
            Settings::KEY_SECONDARY_DISPLAY_COM_PORT,
            Settings::KEY_SECONDARY_DISPLAY_X,
            Settings::KEY_SECONDARY_DISPLAY_Y,
            Settings::KEY_SECONDARY_DISPLAY_WIDTH,
            Settings::KEY_SECONDARY_DISPLAY_HEIGHT,
            Settings::KEY_PASSPORT_READER_ENABLED,
            Settings::KEY_PASSPORT_READER_COM_PORT,
            Settings::KEY_PASSPORT_READER_BAUD_RATE,
            Settings::KEY_PASSPORT_READER_PROVIDER,
            Settings::KEY_LOW_STOCK_ALERT,
            Settings::KEY_ENABLE_BARCODE_SCANNER,
            Settings::KEY_ALLOW_DECIMAL_QUANTITIES,
            Settings::KEY_CASH_DRAWER_ENABLED,
            Settings::KEY_QUOTE_VALIDITY_DAYS,
            Settings::KEY_QUOTE_TO_INVOICE_AUTO,
        ];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $allowed)) {
                $this->set($key, $value);
            }
        }

        return true;
    }

    /**
     * Initialize default settings for a new store
     */
    public function initializeStoreSettings(int $storeId): void
    {
        $defaults = [
            Settings::KEY_ORDER_SALE_NUMBER => 0,
            Settings::KEY_ORDER_PURCHASE_NUMBER => 0,
            Settings::KEY_INVOICE_NUMBER => 0,
            Settings::KEY_QUOTE_NUMBER => 0,
            Settings::KEY_PREFIX_ORDER => 'ORD-',
            Settings::KEY_PREFIX_INVOICE => 'INV-',
            Settings::KEY_PREFIX_PURCHASE => 'PUR-',
            Settings::KEY_PREFIX_QUOTE => 'QUO-',
            Settings::KEY_MAX_PRINT_COPIES => 1,
            Settings::KEY_AUTO_PRINT_ORDER => false,
            Settings::KEY_AUTO_PRINT_INVOICE => false,
            Settings::KEY_PRINT_AFTER_PAYMENT => true,
            Settings::KEY_CURRENCY => 'USD',
            Settings::KEY_CURRENCY_SYMBOL => '$',
            Settings::KEY_TAX_RATE => 0,
            Settings::KEY_TAX_INCLUSIVE => false,
        ];

        foreach ($defaults as $key => $value) {
            Settings::set($key, $value, $storeId);
        }
    }
}
