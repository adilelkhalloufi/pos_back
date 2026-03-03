<?php

namespace App\Repositories\Purchase;

use App\Models\OrderPurchaseItems;
use App\Repositories\BaseRepository;

class PurchaseItemsRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return OrderPurchaseItems::class;
    }
}