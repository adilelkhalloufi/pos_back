<?php

namespace App\Http\Controllers\api;

use App\ApiTraitPagenation;
use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\POSResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\StoreProducts;
use App\Services\Product\Exceptions\ProductNotFoundException;
use App\Services\Product\ProductService;
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
            'purchases' => $product->purchase,
            'sales' => $product->sales,
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

        }catch (ProductNotFoundException $e) {

            return response()->json(['error' => __('product_errors_not_found')], 404);

        } 
        catch (\Exception $e) {

            return response()->json(['error' => __('product_errors_failed_to_update'), 'message' => $e->getMessage()], 500);
        
        }

 

        return response()->json([
            'message' => __('product_successfully_updated'),
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
}
