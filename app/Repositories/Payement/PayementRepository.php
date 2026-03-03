<?php

namespace App\Repositories\Payement;

use App\Models\Payemnt;
use App\Repositories\BaseRepository;

class PayementRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Payemnt::class;
    }
}
