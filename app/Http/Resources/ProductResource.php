<?php

namespace App\Http\Resources;

use App\Enums\AUTOCOMPLETE;
use App\Models\OrderItems;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            Product::COL_ID => $this->id,
            Product::COL_REFERENCE => $this->reference,
            Product::COL_CODEBAR => $this->codebar,
            Product::COL_SLUG => $this->slug,
            Product::COL_STOCK_MIN => $this->stock_min,
            Product::COL_STOCK_MAX => $this->stock_max,
            Product::COL_IS_ACTIVE => $this->is_active,
            Product::COL_ARCHIVE => $this->archive,
            Product::COL_NAME => $this->name,
            Product::COL_DESCRIPTION => $this->description,
            Product::COL_PRICE => (float) $this->price,
            Product::COL_IMAGE => $this->image ? asset('storage/' . $this->image) : null,
            Product::COL_CREATED_AT => $this->created_at,
            Product::COL_CREATED_AT => $this->created_at,
            OrderItems::COL_PRODUCT_ID => $this->id, // this for order items
             Product::COL_CATEGORY_ID => $this->category_id,
            AUTOCOMPLETE::VALUE->value => $this->id,
        
            'qte' => 1, // this for front end to calcluat the product selected
            'category' => CategoryResource::make($this->category),
             'sales' => $this->whenLoaded('sales') ?? [],
            'purchases' => $this->whenLoaded('purchases') ?? [],
            'store' => $this->whenLoaded('store') ?? null,

        ];
    }
}
