<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StorefrontOrderRequest;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreProducts;
use App\Models\StorefrontProduct;
use App\Services\Storefront\StorefrontOrderService;
use RuntimeException;

class StorefrontController extends BaseController
{
    public function __construct(
        private readonly StorefrontOrderService $storefrontOrderService
    ) {
        parent::__construct();
    }

    public function show(string $slug)
    {
        $store = Store::query()
            ->where(Store::COL_PUBLIC_SLUG, $slug)
            ->where(Store::COL_STOREFRONT_ENABLED, true)
            ->first();

        if (!$store instanceof Store) {
            return response()->json(['message' => 'Storefront not found.'], 404);
        }

        $products = StorefrontProduct::query()
            ->where(StorefrontProduct::COL_STORE_ID, $store->id)
            ->where(StorefrontProduct::COL_IS_PUBLISHED, true)
            ->with([
                'product.category',
                'product.variants' => fn($query) => $query
                    ->where('store_id', $store->id)
                    ->where('is_active', true)
                    ->orderByDesc('id'),
            ])
            ->orderBy(StorefrontProduct::COL_SORT_ORDER)
            ->orderByDesc('id')
            ->get();

        $stockByProductId = StoreProducts::query()
            ->where(StoreProducts::COL_STORE_ID, $store->id)
            ->whereIn(StoreProducts::COL_PRODUCT_ID, $products->pluck('product_id')->all())
            ->get()
            ->keyBy(StoreProducts::COL_PRODUCT_ID);

        $products = $products->map(function (StorefrontProduct $row) use ($stockByProductId) {
                $product = $row->product;
                if (!$product instanceof Product) {
                    return null;
                }

                $storeProduct = $stockByProductId->get($product->id);

                $basePrice = (float) ($storeProduct?->price ?? $product->price_sell_1 ?? $product->price ?? 0);

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $row->short_description ?: $product->description,
                    'image' => $row->cover_image ?: $product->image,
                    'category_id' => $product->category_id,
                    'price' => $basePrice,
                    'stock' => (float) ($storeProduct?->stock ?? 0),
                    'is_stockable' => (bool) $product->is_stockable,
                    'variants' => $product->variants->map(function ($variant) use ($basePrice) {
                        return [
                            'id' => $variant->id,
                            'sku' => $variant->sku,
                            'color' => $variant->color,
                            'size' => $variant->size,
                            'material' => $variant->material,
                            'gemstone' => $variant->gemstone,
                            'weight_grams' => (float) $variant->weight_grams,
                            'attributes' => $variant->attributes ?? [],
                            'price' => (float) ($variant->price ?? $basePrice),
                            'stock' => (float) $variant->stock,
                        ];
                    })->values(),
                ];
            });
        $products = $products
            ->filter()
            ->values();

        return response()->json([
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->public_slug,
                'title' => $store->storefront_title ?: $store->name,
                'description' => $store->storefront_description,
                'banner' => $store->storefront_banner,
                'phone' => $store->phone,
                'address' => $store->address,
            ],
            'products' => $products,
        ]);
    }

    public function storeOrder(StorefrontOrderRequest $request, string $slug)
    {
        $store = Store::query()
            ->where(Store::COL_PUBLIC_SLUG, $slug)
            ->where(Store::COL_STOREFRONT_ENABLED, true)
            ->first();

        if (!$store instanceof Store) {
            return response()->json(['message' => 'Storefront not found.'], 404);
        }

        try {
            $order = $this->storefrontOrderService->create($store, $request->validated());
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Order placed successfully.',
            'order' => $order,
        ], 201);
    }
}
