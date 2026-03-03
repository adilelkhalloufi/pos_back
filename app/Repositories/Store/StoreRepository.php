<?php

namespace App\Repositories\Store;

use App\Models\Store;
use App\Repositories\BaseRepository;

class StoreRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Store::class;
    }

 
  

    
}