<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseDeliveryItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_delivery_id' => $this->purchase_delivery_id,
            'order_purchase_item_id' => $this->order_purchase_item_id,
            'product_id' => $this->product_id,
            'ordered_quantity' => $this->ordered_quantity,
            'delivered_quantity' => $this->delivered_quantity,
            'accepted_quantity' => $this->accepted_quantity,
            'rejected_quantity' => $this->rejected_quantity,
            'unit_price' => (float) $this->unit_price,
            'total_price' => (float) $this->total_price,
            'rejection_reason' => $this->rejection_reason,
            'batch_number' => $this->batch_number,
            'expiry_date' => $this->expiry_date,
            'product' => $this->whenLoaded('product', function () {
                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'codebar' => $this->product->codebar,
                ];
            }),
        ];
    }
}
