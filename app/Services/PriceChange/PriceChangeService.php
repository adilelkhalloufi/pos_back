<?php

namespace App\Services\PriceChange;

use App\Models\PriceChangeLog;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class PriceChangeService
{
    /** @return PriceChangeLog[] */
    public function applyBatch(array $productIds, array $prices, ?string $effectiveDate, ?string $reason): array
    {
        $priceFields = ['price_buy', 'price_sell_1'];
        $newPrices = array_filter(
            array_intersect_key($prices, array_flip($priceFields)),
            fn($v) => $v !== null
        );

        if (empty($newPrices)) {
            throw new \InvalidArgumentException('At least one price field must be provided.');
        }

        $logs = [];

        DB::transaction(function () use ($productIds, $newPrices, $effectiveDate, $reason, &$logs) {
            $storeId = currentStoreId();
            $userId = auth()->id();

            $products = Product::whereIn(Product::COL_ID, $productIds)->get();

            foreach ($products as $product) {
                foreach ($newPrices as $field => $newValue) {
                    $oldValue = $product->getAttribute($field);

                    if ((float) $oldValue === (float) $newValue) {
                        continue;
                    }

                    $logs[] = PriceChangeLog::create([
                        PriceChangeLog::COL_PRODUCT_ID     => $product->id,
                        PriceChangeLog::COL_USER_ID        => $userId,
                        PriceChangeLog::COL_FIELD          => $field,
                        PriceChangeLog::COL_OLD_VALUE      => $oldValue,
                        PriceChangeLog::COL_NEW_VALUE      => $newValue,
                        PriceChangeLog::COL_EFFECTIVE_DATE => $effectiveDate,
                        PriceChangeLog::COL_REASON         => $reason,
                        PriceChangeLog::COL_STORE_ID       => $storeId,
                    ]);
                }

                $product->update(array_map('floatval', $newPrices));
            }
        });

        return $logs;
    }

    public function history(int $productId)
    {
        return PriceChangeLog::where(PriceChangeLog::COL_PRODUCT_ID, $productId)
            ->with('user')
            ->orderByDesc(PriceChangeLog::COL_ID)
            ->get();
    }
}
