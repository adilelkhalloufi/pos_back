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
            OrderSale::COL_DISCOUNT => $this->discount,
            OrderSale::COL_ADVANCE => (float)$this->advance,
            OrderSale::COL_TOTAL_PAYMENT => (float)$this->total_payment,
            OrderSale::COL_REST_A_PAY => (float)$this->rest_a_pay,
            OrderSale::COL_IS_INVOICE => $this->is_invoice,
            OrderSale::COL_INVOICE_TOTAL => (float)$this->invoice_total,
            OrderSale::COL_STATUS => EnumPayementStatue::tryFrom($this->status)?->getLabel() ?? 'N/A',
            'customer' => $this->customer,
            'details' => $this->orderItems,
            'payments' => $this->payments,
            'user' => $this->user,
            'items' => $this->orderItems,
            'vendor' => $this->user,
            OrderSale::COL_NOTE => $this->note,
            OrderSale::COL_CANCELLED_AT => $this->cancelled_at?->format('Y-m-d H:i'),
            OrderSale::COL_CANCELLED_BY => $this->cancelledBy,
            OrderSale::COL_CANCEL_REASON => $this->cancel_reason,
            'is_cancelled' => $this->isCancelled(),






        ];
    }
}
