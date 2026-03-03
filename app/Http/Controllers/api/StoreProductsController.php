<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\StoreProductResource;
use App\Models\StoreProducts;
use App\Services\StoreProduct\Exceptions\StoreProductNotFoundException;
use App\Services\StoreProduct\StoreProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StoreProductsController extends BaseController
{
    public function __construct(
        private readonly StoreProductService $storeProductService
    ) {
        parent::__construct();
    }

    /**
     * Get all products for current store
     */
    public function index(Request $request)
    {
        $storeId = $this->storeId();

        $relations = ['product.category', 'product.brand'];
        $products = $this->storeProductService->getStoreProducts($storeId, $relations);

        return response()->json(StoreProductResource::collection($products), Response::HTTP_OK);
    }

    /**
     * Get products with stock > 0 (for POS)
     */
    public function inStock(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], Response::HTTP_NOT_FOUND);
        }

        $search = $request->input('search');
        $products = $this->storeProductService->getInStockProducts($storeId, $search);

        return response()->json(StoreProductResource::collection($products), Response::HTTP_OK);
    }

    /**
     * Get low stock products
     */
    public function lowStock(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], Response::HTTP_NOT_FOUND);
        }

        $threshold = $request->input('threshold', 10);
        $products = $this->storeProductService->getLowStockProducts($storeId, $threshold);

        return response()->json(StoreProductResource::collection($products), Response::HTTP_OK);
    }

    /**
     * Create or update store product
     */
    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        try {
            $storeProduct = $this->storeProductService->createOrUpdate($validated);

            return response()->json([
                'store_product' => new StoreProductResource($storeProduct),
                'message' => 'Store product created/updated successfully',
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to create/update store product',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Show specific store product with related data
     */
    public function show(int $id)
    {   
        try {
            $storeProduct = StoreProducts::with([
                'product.category',
                'product.brand',
                 'store'
            ])->find($id);

            if (!$storeProduct) {
                return response()->json(['error' => 'Store product not found'], Response::HTTP_NOT_FOUND);
            }

            // Get all stores that have this product
            $stores = StoreProducts::where(StoreProducts::COL_PRODUCT_ID, $storeProduct->{StoreProducts::COL_PRODUCT_ID})
                ->with('store')
                ->get();

            return response()->json([
                'store_product' => new StoreProductResource($storeProduct),
                'stores' => $stores,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch store product',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get store product by store and product ID
     */
    public function getByStoreAndProduct(Request $request)
    {
        $storeId = $request->input('store_id', $this->storeId());
        $productId = $request->input('product_id');

        if (!$storeId || !$productId) {
            return response()->json(['error' => 'Store ID and Product ID are required'], Response::HTTP_BAD_REQUEST);
        }

        $storeProduct = $this->storeProductService->findByStoreAndProduct($storeId, $productId);

        if (!$storeProduct) {
            return response()->json(['error' => 'Store product not found'], Response::HTTP_NOT_FOUND);
        }

        return response()->json(new StoreProductResource($storeProduct), Response::HTTP_OK);
    }

    /**
     * Update store product
     */
    public function update(StoreProductRequest $request, int $id)
    {
        $validated = $request->validated();

        try {
            $this->storeProductService->update($id, $validated);

            return response()->json([
                'message' => 'Store product updated successfully',
            ], Response::HTTP_OK);

        } catch (StoreProductNotFoundException $e) {
            return response()->json(['error' => 'Store product not found'], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update store product',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update stock quantity
     */
    public function updateStock(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',
            'operation' => 'required|in:add,subtract,set',
        ]);

        try {
            $updated = $this->storeProductService->updateStock(
                $request->input('store_id'),
                $request->input('product_id'),
                $request->input('quantity'),
                $request->input('operation', 'set')
            );

            if (!$updated) {
                return response()->json(['error' => 'Failed to update stock'], Response::HTTP_BAD_REQUEST);
            }

            return response()->json([
                'message' => 'Stock updated successfully',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update stock',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Delete store product
     */
    public function destroy(int $id)
    {
        try {
            $deleted = $this->storeProductService->delete($id);

            if (!$deleted) {
                return response()->json(['error' => 'Store product not found'], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'message' => 'Store product deleted successfully',
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete store product',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
