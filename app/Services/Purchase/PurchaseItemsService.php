<?php

namespace App\Services\Purchase;

use App\Models\OrderPurchaseItems;
use App\Repositories\Purchase\PurchaseItemsRepository;

class PurchaseItemsService
{
     public function __construct(
        private readonly PurchaseItemsRepository $purchaseItemsRepository
     )
    {}


    public function findById(int $id)
    {
        return $this->purchaseItemsRepository->find($id);
    }

   
    public function create(array $attributes) :? OrderPurchaseItems
    {
        $purchase = $this->purchaseItemsRepository->create([
            OrderPurchaseItems::COL_PURCHASE_ID => $attributes[OrderPurchaseItems::COL_PURCHASE_ID],
            OrderPurchaseItems::COL_PRODUCT_ID => $attributes[OrderPurchaseItems::COL_PRODUCT_ID],
            OrderPurchaseItems::COL_QUANTITY => $attributes[OrderPurchaseItems::COL_QUANTITY],
            OrderPurchaseItems::COL_PRICE => $attributes[OrderPurchaseItems::COL_PRICE],
            OrderPurchaseItems::COL_TOTAL => $attributes[OrderPurchaseItems::COL_TOTAL],
        ]);

        

        return $purchase;
    }
}