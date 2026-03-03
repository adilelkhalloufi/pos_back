<?php

namespace App\Http\Resources;

use App\Enums\EnumOrderStatue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderPurchaseResource extends JsonResource
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
            'order_number' => $this->order_number,
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'reference' => $this->reference,
            'status' => $this->status !== null && EnumOrderStatue::tryFrom($this->status) !== null
            ? EnumOrderStatue::from($this->status)->getLabel()
            : 'Unknown',
            'color' => $this->status !== null && EnumOrderStatue::tryFrom($this->status) !== null
                ? EnumOrderStatue::from($this->status)->getColor()
                : '#999999',
            'supplier' => $this->supplier,
            'supplier_id' => $this->supplier->id,
            'details' => $this->orderItems,
            'payments' => $this->payments,
            'user' => $this->user,
            'store' => $this->payment_term,
            'public_note' => $this->public_note,
            'private_note' => $this->private_note,
            'paid_method_id' => $this->paid_method_id,



        ];
    }
}
