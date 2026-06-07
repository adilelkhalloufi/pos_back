<?php

namespace App\Services\Purchase;

use App\Models\OrderPurchase;
use App\Models\OrderPurchaseItems;
use App\Repositories\Purchase\PurchaseItemsRepository;
use Illuminate\Support\Facades\DB;

class PurchaseItemsService
{
    public function __construct(
        private readonly PurchaseItemsRepository $purchaseItemsRepository
    ) {}


    public function findById(int $id)
    {
        return $this->purchaseItemsRepository->find($id);
    }


    public function create(array $attributes): ?OrderPurchaseItems
    {
        return DB::transaction(function () use ($attributes) {
            $priceHt = $attributes[OrderPurchaseItems::COL_PRICE];
            $quantity = $attributes[OrderPurchaseItems::COL_QUANTITY];
            $tvaRate = $attributes[OrderPurchaseItems::COL_TVA] ?? 20; // Default TVA 20%

            $tva = ($priceHt * $quantity) * ($tvaRate / 100);
            $priceTtc = $priceHt + ($priceHt * ($tvaRate / 100));
            $total = $priceTtc * $quantity;

            $item = $this->purchaseItemsRepository->create([
                OrderPurchaseItems::COL_ORDER_ID => $attributes[OrderPurchaseItems::COL_ORDER_ID],
                OrderPurchaseItems::COL_PRODUCT_ID => $attributes[OrderPurchaseItems::COL_PRODUCT_ID],
                OrderPurchaseItems::COL_QUANTITY => $quantity,
                OrderPurchaseItems::COL_PRICE => $priceHt,
                OrderPurchaseItems::COL_PRICE_HT => $priceHt,
                OrderPurchaseItems::COL_TVA => $tvaRate,
                OrderPurchaseItems::COL_PRICE_TTC => $priceTtc,
                OrderPurchaseItems::COL_TOTAL => $total,
            ]);

            // Update the parent order totals
            $order = OrderPurchase::find($attributes[OrderPurchaseItems::COL_ORDER_ID]);
            if ($order) {
                $order->increment(OrderPurchase::COL_TOTAL_HT, $priceHt * $quantity);
                $order->increment(OrderPurchase::COL_TOTAL_TVA, $tva);
                $order->increment(OrderPurchase::COL_TOTAL_TTC, $total);
            }

            return $item;
        });
    }
}
