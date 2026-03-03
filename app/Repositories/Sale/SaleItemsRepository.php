<?php

namespace App\Repositories\Sale;

use App\Models\OrderItems;
use App\Repositories\BaseRepository;

class SaleItemsRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return OrderItems::class;
    }
}
