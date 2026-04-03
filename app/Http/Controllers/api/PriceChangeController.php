<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Http\Requests\PriceChangeRequest;
use App\Models\OrderItems;
use App\Models\PriceChangeLog;
use App\Models\Product;
use App\Services\PriceChange\PriceChangeService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class PriceChangeController extends BaseController
{
    public function __construct(private readonly PriceChangeService $priceChangeService)
    {
        parent::__construct();
    }

    /**
     * POST /price-changes
     * Apply a batch price change to one or many products.
     */
    public function store(PriceChangeRequest $request)
    {
        try {
            $changeType = $request->input('change_type');
            $startDate = $request->input('start_date');
            $modificationType = $request->input('modification_type');
            
            $productsToUpdate = [];
            
            // Determine which products to update based on change_type
            if ($changeType === 'category') {
                $categoryValues = $request->input('category_values');
                
                foreach ($categoryValues as $categoryData) {
                    $categoryId = $categoryData['id'];
                    $value = $categoryData['value'];
                    
                    // Get all products in this category
                    $products = Product::where('category_id', $categoryId)->get();
                    
                    foreach ($products as $product) {
                        $productsToUpdate[] = [
                            'product' => $product,
                            'value' => $value,
                        ];
                    }
                }
            } else { // article
                $productIds = $request->input('product_ids');
                $modificationValue = $request->input('modification_value');
                
                $products = Product::whereIn('id', $productIds)->get();
                
                foreach ($products as $product) {
                    $productsToUpdate[] = [
                        'product' => $product,
                        'value' => $modificationValue,
                    ];
                }
            }
            
            // Apply price changes
            $updatedProducts = [];
            
            DB::transaction(function () use ($productsToUpdate, $modificationType, $startDate, &$updatedProducts) {
                foreach ($productsToUpdate as $data) {
                    $product = $data['product'];
                    $value = $data['value'];
                    
                    $oldPrice = $product->price;
                    $newPrice = null;
                    
                    // Calculate new price based on modification_type
                    if ($modificationType === 'amount') {
                        $newPrice = $oldPrice - $value;
                    } else { // percentage
                        $priceSell = $product->price_sell_1 ?? 0;
                        $priceBuy = $product->price_buy ?? 0;
                        $reduction = ceil(($priceSell - $priceBuy) * ($value / 100));
                        $newPrice = $priceSell - $reduction;
                    }
                    
                    // Ensure price doesn't go negative
                    $newPrice = max(0, $newPrice);
                    
                    // Update product
                    $product->price_sell_1 = $newPrice;
                    $product->save();
                    
                    $updatedProducts[] = [
                        'product_id' => $product->id,
                        'old_price' => $oldPrice,
                        'new_price' => $newPrice,
                    ];
                    
                    // Log the price change
                    PriceChangeLog::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'field' => 'price_sell_1',
                        'old_value' => $oldPrice,
                        'new_value' => $newPrice,
                        'effective_date' => $startDate,
                        'reason' => 'Batch price change',
                        'store_id' => currentStoreId(),
                    ]);
                    
                    // Update existing order items with created_at >= start_date
                    OrderItems::where('product_id', $product->id)
                        ->where('created_at', '>=', $startDate)
                        ->update(['invoice_price' => $newPrice]);
                }
            });
            
            return  response()->json([
                'message' => 'Price changes applied successfully',
                'data' => $updatedProducts,
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return  response()->json([
                'message' => 'An error occurred while applying price changes',
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

   
}
