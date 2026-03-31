<?php

namespace App\Http\Resources;

use App\Enums\EnumPayementStatue;
use App\Models\OrderSale;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            OrderSale::COL_ID => $this->id,
            OrderSale::COL_ORDER_NUMBER => padNumber($this->order_number),
            OrderSale::COL_CREATED_AT => $this->created_at->format('Y-m-d H:i'),
            OrderSale::COL_TOTAL_COMMAND => (float) $this->total_command,
            'items' => $this->orderItems,
            'vendor' => $this->user,
            OrderSale::COL_NOTE => $this->note,
 
        ];
    }
}
