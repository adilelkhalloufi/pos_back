<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductVariantRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class ProductVariantController extends BaseController
{
    public function index(Request $request, Product $product)
    {
        $storeId = $this->storeId();

        if ((int) $product->store_id !== (int) $storeId) {
            return response()->json(['message' => 'Product is not in current store.'], 404);
        }

        $variants = ProductVariant::query()
            ->where(ProductVariant::COL_PRODUCT_ID, $product->id)
            ->where(ProductVariant::COL_STORE_ID, $storeId)
            ->orderByDesc('id')
            ->get();

        return response()->json($variants);
    }

    public function store(ProductVariantRequest $request, Product $product)
    {
        $storeId = $this->storeId();

        if ((int) $product->store_id !== (int) $storeId) {
            return response()->json(['message' => 'Product is not in current store.'], 404);
        }

        $variant = ProductVariant::create([
            ...$request->validated(),
            ProductVariant::COL_PRODUCT_ID => $product->id,
            ProductVariant::COL_STORE_ID => $storeId,
        ]);

        return response()->json($variant, 201);
    }

    public function update(ProductVariantRequest $request, Product $product, ProductVariant $variant)
    {
        $storeId = $this->storeId();

        if (
            (int) $product->store_id !== (int) $storeId
            || (int) $variant->product_id !== (int) $product->id
            || (int) $variant->store_id !== (int) $storeId
        ) {
            return response()->json(['message' => 'Variant not found in current store product.'], 404);
        }

        $variant->update($request->validated());

        return response()->json($variant->fresh());
    }

    public function destroy(Product $product, ProductVariant $variant)
    {
        $storeId = $this->storeId();

        if (
            (int) $product->store_id !== (int) $storeId
            || (int) $variant->product_id !== (int) $product->id
            || (int) $variant->store_id !== (int) $storeId
        ) {
            return response()->json(['message' => 'Variant not found in current store product.'], 404);
        }

        $variant->delete();

        return response()->json(['message' => 'Variant deleted successfully.']);
    }
}
