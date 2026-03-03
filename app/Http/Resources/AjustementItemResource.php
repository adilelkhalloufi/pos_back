<?php

namespace App\Http\Resources;

use App\Models\AjustementItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AjustementItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            AjustementItem::COL_ID => $this->id,
            AjustementItem::COL_AJUSTEMENT_ID => $this->ajustement_id,
            AjustementItem::COL_PRODUCT_ID => $this->product_id,
            AjustementItem::COL_TYPE => $this->type,
            AjustementItem::COL_QUANTITY => (float) $this->quantity,
            AjustementItem::COL_PREVIOUS_STOCK => (float) $this->previous_stock,
            AjustementItem::COL_NEW_STOCK => (float) $this->new_stock,
            AjustementItem::COL_NOTE => $this->note,
            
            'product' => ProductResource::make($this->whenLoaded('product')),
        ];
    }
}
