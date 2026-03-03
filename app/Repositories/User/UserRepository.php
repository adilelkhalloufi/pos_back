<?php

namespace App\Repositories\User;

use App\Models\Store;
use App\Models\User;
use App\Repositories\BaseRepository;

class UserRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return User::class;
    }

    public function getUsersByStoreId(int $storeId)
    {
        return  Store::find($storeId)->workers;
    }
}