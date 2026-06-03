<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseDeliveryResource extends JsonResource
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
            'delivery_number' => $this->delivery_number,
            'order_purchase_id' => $this->order_purchase_id,
            'delivery_date' => $this->delivery_date,
            'supplier_delivery_note' => $this->supplier_delivery_note,
            'transport_company' => $this->transport_company,
            'driver_name' => $this->driver_name,
            'vehicle_plate' => $this->vehicle_plate,
            'delivery_note' => $this->delivery_note,
            'quality_check_note' => $this->quality_check_note,
            'has_issues' => $this->has_issues,
            'status' => $this->status,
            'status_label' => $this->getStatusLabel(),
            'status_color' => $this->getStatusColor(),
            'created_at' => $this->created_at->format('Y-m-d H:i'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i'),
            'order_purchase' => $this->whenLoaded('orderPurchase'),
            'delivery_items' => PurchaseDeliveryItemResource::collection($this->whenLoaded('deliveryItems')),
            'received_by' => $this->whenLoaded('receivedBy', function () {
                return [
                    'id' => $this->receivedBy->id,
                    'name' => $this->receivedBy->name,
                ];
            }),
            'supplier' => $this->whenLoaded('supplier'),
            'store' => $this->whenLoaded('store'),
        ];
    }

    /**
     * Get status label
     */
    private function getStatusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Brouillon',
            'validated' => 'Validé',
            'cancelled' => 'Annulé',
            default => 'Inconnu',
        };
    }

    /**
     * Get status color
     */
    private function getStatusColor(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'validated' => 'green',
            'cancelled' => 'red',
            default => 'gray',
        };
    }
}
