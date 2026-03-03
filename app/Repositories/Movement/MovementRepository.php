<?php

namespace App\Repositories\Movement;

use App\Models\StockMovement;
use App\Repositories\BaseRepository;

class MovementRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return StockMovement::class;
    }
}