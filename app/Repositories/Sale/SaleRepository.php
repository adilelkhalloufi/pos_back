<?php

namespace App\Repositories\Sale;

use App\Models\OrderSale;
use App\Repositories\BaseRepository;

class SaleRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return OrderSale::class;
    }
}
