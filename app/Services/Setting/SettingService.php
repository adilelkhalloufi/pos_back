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
}
