<?php

namespace App\Http\Controllers\api;

use App\ApiTraitPagenation;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\POSResource;
use App\Http\Resources\ProductResource;
use App\Models\OrderPurchaseItems;
use App\Models\Product;
use App\Models\StoreProducts;
use App\Services\Product\Exceptions\ProductNotFoundException;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseController
{
    use ApiTraitPagenation;

    private ProductService $productService;

    public function __construct(ProductService $productService)
    {
        parent::__construct();
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $storeId = $this->storeId();

        if (!$storeId) {
            return response()->json(['error' => 'No store found'], 404);
        }

        $product = Product::where(Product::COL_STORE_ID, $storeId)
            ->with(['barcodes', 'unit', 'sellUnit', 'components.component', 'components.unit'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(ProductResource::collection($product), 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        $validated = $request->validated();

        try {

            $product =  $this->productService->create($validated);

            // Load barcodes relationship
            $product->load(['barcodes', 'unit', 'sellUnit', 'components.component', 'components.unit']);
        } catch (\Exception $e) {

            return response()->json(['error' => __('product.errors.failed_to_create'), 'message' => $e->getMessage()], 500);
        }

        return response()->json(new ProductResource($product), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        try {
            $product =  $this->productService->findwithRelations($id);
            $stores = StoreProducts::where(StoreProducts::COL_PRODUCT_ID, $id)->with('store')->get();
        } catch (ProductNotFoundException $e) {
            return response()->json(['error' => __('product_errors_not_found')], 404);
        }

        return response()->json([
            'product' => new ProductResource($product),

            'stores' => $stores,
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id)
    {



        $validated = $request->validated();

        try {

            $product =  $this->productService->update($id, $validated);

            // Load barcodes to return in response
            $product?->load(['barcodes', 'unit', 'sellUnit', 'components.component', 'components.unit']);
        } catch (ProductNotFoundException $e) {

            return response()->json(['error' => __('product_errors_not_found')], 404);
        } catch (\Exception $e) {

            return response()->json(['error' => __('product_errors_failed_to_update'), 'message' => $e->getMessage()], 500);
        }



        return response()->json([
            'message' => __('product_successfully_updated'),
            'product' => $product ? new ProductResource($product) : null,
        ], 200);
    }

    /**
     * Get products for POS (Point of Sale)
     * Returns products with stock available in the current store
     */
    public function pos(Request $request)
    {
        $storeId = $this->storeId();
        $search = $request->query('search');

        try {
            $products = $this->productService->getProductsForPOS($storeId, $search);

            return response()->json(POSResource::collection($products), 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch products',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get purchase history for a specific product.
     * Endpoint: /products/{id}/purchases?from=YYYY-MM-DD&to=YYYY-MM-DD&supplier_id={id}
     */
    public function purchases(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date', 'before_or_equal:to'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
        ]);

        $product = Product::query()
            ->with('unit')
            ->when($this->storeId(), fn ($query) => $query->where(Product::COL_STORE_ID, $this->storeId()))
            ->whereKey($id)
            ->firstOrFail();

        $query = OrderPurchaseItems::query()
            ->with(['order.supplier', 'product'])
            ->where('product_id', $product->id)
            ->join('order_purchases', 'order_purchases.id', '=', 'order_purchase_items.order_id')
            ->select('order_purchase_items.*')
            ->where('order_purchases.store_id', $product->store_id ?? $this->storeId());

        if (! empty($validated['supplier_id'])) {
            $query->where('order_purchases.supplier_id', $validated['supplier_id']);
        }

        if (! empty($validated['from'])) {
            $query->whereDate('order_purchases.created_at', '>=', $validated['from']);
        }

        if (! empty($validated['to'])) {
            $query->whereDate('order_purchases.created_at', '<=', $validated['to']);
        }

        $purchases = $query
            ->orderBy('order_purchases.created_at', 'desc')
            ->get()
            ->map(function (OrderPurchaseItems $item) {
                $order = $item->order;

                return [
                    'id' => $item->id,
                    'order_id' => $order?->id,
                    'order_number' => $order?->order_number,
                    'supplier_id' => $order?->supplier_id,
                    'supplier' => $order?->supplier,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product?->name ?? $item->name,
                    'purchase_unit' => $item->product?->relationLoaded('unit') ? $item->product->unit : ($item->product?->unit ?? null),
                    'sell_unit' => $item->product?->relationLoaded('sellUnit') ? $item->product->sellUnit : ($item->product?->sellUnit ?? null),
                    'unit' => $item->product?->relationLoaded('unit') ? $item->product->unit : ($item->product?->unit ?? null),
                    'quantity' => (int) $item->quantity,
                    'price' => (float) $item->price,
                    'total' => (float) $item->total,
                    'created_at' => $order?->created_at?->format('Y-m-d H:i:s'),
                ];
            });

        return response()->json([
            'product' => new ProductResource($product),
            'purchases' => $purchases,
        ], 200);
    }
}
