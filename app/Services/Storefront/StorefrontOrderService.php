<?php

namespace App\Services\Storefront;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreProducts;
use App\Models\StorefrontOrder;
use App\Models\StorefrontOrderItem;
use App\Models\StorefrontProduct;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class StorefrontOrderService
{
    public function create(Store $store, array $attributes): StorefrontOrder
    {
        if (!$store->getAttribute(Store::COL_STOREFRONT_ENABLED)) {
            throw new RuntimeException('Storefront is disabled for this store.');
        }

        return DB::transaction(function () use ($store, $attributes) {
            $orderNumber = $this->generateOrderNumber((int) $store->id);
            $subtotal = 0.0;

            $order = StorefrontOrder::create([
                StorefrontOrder::COL_STORE_ID => $store->id,
                StorefrontOrder::COL_ORDER_NUMBER => $orderNumber,
                StorefrontOrder::COL_CUSTOMER_NAME => $attributes['customer_name'],
                StorefrontOrder::COL_CUSTOMER_PHONE => $attributes['customer_phone'],
                StorefrontOrder::COL_CUSTOMER_EMAIL => $attributes['customer_email'] ?? null,
                StorefrontOrder::COL_CUSTOMER_ADDRESS => $attributes['customer_address'] ?? null,
                StorefrontOrder::COL_NOTE => $attributes['note'] ?? null,
                StorefrontOrder::COL_STATUS => 'pending',
                StorefrontOrder::COL_SUBTOTAL => 0,
                StorefrontOrder::COL_TOTAL => 0,
                StorefrontOrder::COL_CURRENCY => $attributes['currency'] ?? 'MAD',
                StorefrontOrder::COL_PLACED_AT => now(),
            ]);

            foreach ($attributes['items'] as $item) {
                $prepared = $this->prepareOrderItem($store, $item);
                $lineTotal = $prepared['unit_price'] * $prepared['quantity'];
                $subtotal += $lineTotal;

                StorefrontOrderItem::create([
                    StorefrontOrderItem::COL_ORDER_ID => $order->id,
                    StorefrontOrderItem::COL_PRODUCT_ID => $prepared['product']->id,
                    StorefrontOrderItem::COL_VARIANT_ID => $prepared['variant']?->id,
                    StorefrontOrderItem::COL_PRODUCT_NAME => $prepared['product']->name ?? 'Product',
                    StorefrontOrderItem::COL_VARIANT_NAME => $prepared['variant_name'],
                    StorefrontOrderItem::COL_SKU => $prepared['sku'],
                    StorefrontOrderItem::COL_COLOR => $prepared['color'],
                    StorefrontOrderItem::COL_SIZE => $prepared['size'],
                    StorefrontOrderItem::COL_MATERIAL => $prepared['material'],
                    StorefrontOrderItem::COL_QUANTITY => $prepared['quantity'],
                    StorefrontOrderItem::COL_UNIT_PRICE => $prepared['unit_price'],
                    StorefrontOrderItem::COL_LINE_TOTAL => $lineTotal,
                ]);
            }

            $order->update([
                StorefrontOrder::COL_SUBTOTAL => $subtotal,
                StorefrontOrder::COL_TOTAL => $subtotal,
            ]);

            return $order->load('items');
        });
    }

    private function prepareOrderItem(Store $store, array $item): array
    {
        $product = Product::query()
            ->where('id', $item['product_id'])
            ->where(Product::COL_STORE_ID, $store->id)
            ->first();

        if (!$product instanceof Product) {
            throw new RuntimeException('Product not found in this store.');
        }

        $isPublished = StorefrontProduct::query()
            ->where(StorefrontProduct::COL_STORE_ID, $store->id)
            ->where(StorefrontProduct::COL_PRODUCT_ID, $product->id)
            ->where(StorefrontProduct::COL_IS_PUBLISHED, true)
            ->exists();

        if (!$isPublished) {
            throw new RuntimeException('Product is not published in storefront.');
        }

        $quantity = (float) $item['quantity'];
        $storeProduct = StoreProducts::query()
            ->where(StoreProducts::COL_STORE_ID, $store->id)
            ->where(StoreProducts::COL_PRODUCT_ID, $product->id)
            ->lockForUpdate()
            ->first();

        if (!$storeProduct instanceof StoreProducts) {
            throw new RuntimeException('Store product stock row not found.');
        }

        $variant = null;
        $variantName = null;
        $sku = null;
        $color = null;
        $size = null;
        $material = null;
        $unitPrice = (float) ($storeProduct->price ?? $product->price_sell_1 ?? $product->price ?? 0);

        if (!empty($item['variant_id'])) {
            $variant = ProductVariant::query()
                ->where('id', $item['variant_id'])
                ->where(ProductVariant::COL_PRODUCT_ID, $product->id)
                ->where(ProductVariant::COL_STORE_ID, $store->id)
                ->where(ProductVariant::COL_IS_ACTIVE, true)
                ->lockForUpdate()
                ->first();

            if (!$variant instanceof ProductVariant) {
                throw new RuntimeException('Variant not found or inactive.');
            }

            if ((float) $variant->stock < $quantity) {
                throw new RuntimeException('Not enough variant stock.');
            }

            $variant->decrement(ProductVariant::COL_STOCK, $quantity);

            $unitPrice = (float) ($variant->price ?? $unitPrice);
            $sku = $variant->sku;
            $color = $variant->color;
            $size = $variant->size;
            $material = $variant->material;
            $variantName = trim(implode(' - ', array_filter([$color, $size, $material]))) ?: null;
        } elseif ((bool) $product->is_stockable && (float) $storeProduct->stock < $quantity) {
            throw new RuntimeException('Not enough product stock.');
        }

        if ((bool) $product->is_stockable) {
            $storeProduct->decrement(StoreProducts::COL_STOCK, $quantity);
        }

        return [
            'product' => $product,
            'variant' => $variant,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'variant_name' => $variantName,
            'sku' => $sku,
            'color' => $color,
            'size' => $size,
            'material' => $material,
        ];
    }

    private function generateOrderNumber(int $storeId): string
    {
        $countToday = StorefrontOrder::query()
            ->where(StorefrontOrder::COL_STORE_ID, $storeId)
            ->whereDate(StorefrontOrder::COL_CREATED_AT, today())
            ->count();

        return 'SF-' . now()->format('Ymd') . '-' . str_pad((string) ($countToday + 1), 5, '0', STR_PAD_LEFT);
    }
}
