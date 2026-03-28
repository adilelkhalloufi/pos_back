<?php

namespace App\Http\Resources;

use App\Enums\AUTOCOMPLETE;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\StoreProducts;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, // id of store_product
            'id_store_product' => $this->id, // kept for backward compatibility
            StoreProducts::COL_STORE_ID => $this->{StoreProducts::COL_STORE_ID},
            StoreProducts::COL_PRODUCT_ID => $this->{StoreProducts::COL_PRODUCT_ID},
            StoreProducts::COL_PRICE => (float) $this->{StoreProducts::COL_PRICE},
            StoreProducts::COL_COST => (float) $this->{StoreProducts::COL_COST},
            StoreProducts::COL_STOCK => (float) $this->{StoreProducts::COL_STOCK},
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'store' => StoreResource::make($this->whenLoaded('store')),
            // 'product' => ProductResource::make($this->whenLoaded('product')),

            Product::COL_REFERENCE => $this->product->reference,
            // Product::COL_CODEBAR => $this->product->codebar,
            Product::COL_SLUG => $this->product->slug,
            Product::COL_STOCK_ALERT => $this->product->stock_alert,
            Product::COL_IS_ACTIVE => $this->product->is_active,
            Product::COL_ARCHIVE => $this->product->archive,
            Product::COL_NAME => $this->product->name,
            Product::COL_DESCRIPTION => $this->product->description,
            Product::COL_PRICE => (float) $this->product->price,
            Product::COL_IMAGE => $this->product->image ? asset('storage/' . $this->product->image) : null,
            Product::COL_CREATED_AT => $this->created_at,
            Product::COL_CREATED_AT => $this->created_at,
            OrderItems::COL_PRODUCT_ID => $this->product->id, // this for order items
            Product::COL_CATEGORY_ID => $this->product->category_id,
            AUTOCOMPLETE::VALUE->value => $this->product->id,


            'qte' => 1, // this for front end to calcluat the product selected
            'barcodes' => $this->whenLoaded('product', function () {
                return $this->product->barcodes->pluck('barcode')->toArray();
            }, []),
            'category' => CategoryResource::make($this->product->category),
            'sales' => $this->whenLoaded('product.sales') ?? [],
            'purchases' => $this->whenLoaded('product.purchases') ?? [],
            'store' => $this->whenLoaded('product.store') ?? null,

        ];
    }
}
