<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StorefrontProductRequest;
use App\Models\Product;
use App\Models\Store;
use App\Models\StorefrontOrder;
use App\Models\StorefrontProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorefrontCatalogController extends BaseController
{
    public function settings()
    {
        $store = $this->the_store();

        return response()->json([
            'id' => $store?->id,
            'name' => $store?->name,
            Store::COL_PUBLIC_SLUG => $store?->public_slug,
            Store::COL_STOREFRONT_ENABLED => (bool) $store?->storefront_enabled,
            Store::COL_STOREFRONT_TITLE => $store?->storefront_title,
            Store::COL_STOREFRONT_DESCRIPTION => $store?->storefront_description,
            Store::COL_STOREFRONT_BANNER => $store?->storefront_banner,
        ]);
    }

    public function updateSettings(Request $request)
    {
        $store = $this->the_store();

        $validated = $request->validate([
            Store::COL_PUBLIC_SLUG => 'nullable|string|max:120|unique:stores,public_slug,' . $store->id,
            Store::COL_STOREFRONT_ENABLED => 'nullable|boolean',
            Store::COL_STOREFRONT_TITLE => 'nullable|string|max:255',
            Store::COL_STOREFRONT_DESCRIPTION => 'nullable|string|max:3000',
            Store::COL_STOREFRONT_BANNER => 'nullable|string|max:255',
        ]);

        if (empty($validated[Store::COL_PUBLIC_SLUG])) {
            $validated[Store::COL_PUBLIC_SLUG] = Str::slug($store->name . '-' . $store->id);
        }

        $store->update($validated);

        return response()->json([
            'message' => 'Storefront settings updated successfully.',
            'store' => $store->fresh(),
        ]);
    }

    public function index()
    {
        $storeId = $this->storeId();

        $rows = StorefrontProduct::query()
            ->where(StorefrontProduct::COL_STORE_ID, $storeId)
            ->with([
                'product.category',
                'product.variants' => fn($query) => $query
                    ->where(Product::COL_STORE_ID, $storeId)
                    ->where('is_active', true),
            ])
            ->orderBy(StorefrontProduct::COL_SORT_ORDER)
            ->orderByDesc('id')
            ->get();

        return response()->json($rows);
    }

    public function store(StorefrontProductRequest $request)
    {
        $storeId = $this->storeId();

        $product = Product::query()
            ->where('id', $request->validated('product_id'))
            ->where(Product::COL_STORE_ID, $storeId)
            ->first();

        if (!$product instanceof Product) {
            return response()->json(['message' => 'Product is not in current store.'], 404);
        }

        $entry = StorefrontProduct::updateOrCreate(
            [
                StorefrontProduct::COL_STORE_ID => $storeId,
                StorefrontProduct::COL_PRODUCT_ID => $product->id,
            ],
            $request->validated()
        );

        return response()->json($entry->fresh('product'), 201);
    }

    public function update(Request $request, int $id)
    {
        $storeId = $this->storeId();

        $entry = StorefrontProduct::query()
            ->where('id', $id)
            ->where(StorefrontProduct::COL_STORE_ID, $storeId)
            ->first();

        if (!$entry instanceof StorefrontProduct) {
            return response()->json(['message' => 'Storefront product not found.'], 404);
        }

        $validated = $request->validate([
            'is_published' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'short_description' => 'nullable|string|max:255',
            'cover_image' => 'nullable|string|max:255',
        ]);

        $entry->update($validated);

        return response()->json($entry->fresh('product'));
    }

    public function destroy(int $id)
    {
        $storeId = $this->storeId();

        $entry = StorefrontProduct::query()
            ->where('id', $id)
            ->where(StorefrontProduct::COL_STORE_ID, $storeId)
            ->first();

        if (!$entry instanceof StorefrontProduct) {
            return response()->json(['message' => 'Storefront product not found.'], 404);
        }

        $entry->delete();

        return response()->json(['message' => 'Storefront product deleted successfully.']);
    }

    public function orders()
    {
        $storeId = $this->storeId();

        $orders = StorefrontOrder::query()
            ->where(StorefrontOrder::COL_STORE_ID, $storeId)
            ->with('items')
            ->orderByDesc('id')
            ->get();

        return response()->json($orders);
    }

    public function showOrder(int $id)
    {
        $storeId = $this->storeId();

        $order = StorefrontOrder::query()
            ->where(StorefrontOrder::COL_STORE_ID, $storeId)
            ->where('id', $id)
            ->with('items')
            ->first();

        if (!$order instanceof StorefrontOrder) {
            return response()->json(['message' => 'Storefront order not found.'], 404);
        }

        return response()->json($order);
    }
}
