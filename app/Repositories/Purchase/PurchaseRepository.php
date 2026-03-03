<?php

namespace App\Repositories\Purchase;

use App\Models\OrderPurchase;
use App\Repositories\BaseRepository;

class PurchaseRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return OrderPurchase::class;
    }
}