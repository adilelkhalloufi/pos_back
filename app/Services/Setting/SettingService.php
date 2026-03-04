<?php

namespace App\Services\Setting;

use App\Models\Settings;
use Illuminate\Support\Arr;

class SettingService
{
    // Your service methods go here
    public function __construct() {}

    public function getSettingsByStoreId(int $storeId): ?Settings
    {
        return Settings::where(Settings::COL_STORE_ID, $storeId)->first();
    }



    public function getNextSaleOrderNumber(): string
    {
        $settings = $this->getSettingsByStoreId(currentStoreId());
        $nextNumber = $settings->getAttribute(Settings::COL_ORDER_SALE_NUMBER) + 1;
        return $settings->getAttribute(Settings::COL_PREFIX_ORDER) . str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);
    }
    public function incrementSaleOrderNumber(): void
    {
        $settings = $this->getSettingsByStoreId(currentStoreId());
        $nextNumber = $settings->getAttribute(Settings::COL_ORDER_SALE_NUMBER) + 1;
        $settings->update([Settings::COL_ORDER_SALE_NUMBER => $nextNumber]);
    }
    public function getNextSaleInvoiceNumber(): string
    {
        $settings = $this->getSettingsByStoreId(currentStoreId());
        $nextNumber = $settings->getAttribute(Settings::COL_INVOICE_NUMBER) + 1;
        return $settings->getAttribute(Settings::COL_PREFIX_INVOICE) . str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);
    }
    public function incrementSaleInvoiceNumber(): void
    {
        $settings = $this->getSettingsByStoreId(currentStoreId());
        $nextNumber = $settings->getAttribute(Settings::COL_INVOICE_NUMBER) + 1;
        $settings->update([Settings::COL_INVOICE_NUMBER => $nextNumber]);
    }
    public function getNextPurchaseOrderNumber(): string
    {
        $settings = $this->getSettingsByStoreId(currentStoreId());
        $nextNumber = $settings->getAttribute(Settings::COL_ORDER_PURCHASE_NUMBER) + 1;
        return $settings->getAttribute(Settings::COL_PREFIX_PURCHASE) . str_pad((string)$nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function incrementPurchaseOrderNumber(): void
    {
        $settings = $this->getSettingsByStoreId(currentStoreId());
        $nextNumber = $settings->getAttribute(Settings::COL_ORDER_PURCHASE_NUMBER) + 1;
        $settings->update([Settings::COL_ORDER_PURCHASE_NUMBER => $nextNumber]);
    }

    public function getSettings(): ?Settings
    {
        return $this->getSettingsByStoreId(currentStoreId());
    }

    public function updateSettings(array $attributes): Settings
    {
        $settings = $this->getSettingsByStoreId(currentStoreId());

        $allowed = [
            'company_name', 'currency', 'document_header', 'document_footer',
            'order_prefix', 'invoice_prefix', 'purchase_prefix',
            'max_print_copies',
            'secondary_display_enabled', 'secondary_display_connection',
            'secondary_display_com_port', 'secondary_display_x', 'secondary_display_y',
            'secondary_display_width', 'secondary_display_height',
            'passport_reader_enabled', 'passport_reader_com_port',
            'passport_reader_baud_rate', 'passport_reader_provider',
        ];

        $settings->update(array_intersect_key($attributes, array_flip($allowed)));

        return $settings->fresh();
    }}