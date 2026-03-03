<?php

namespace App\Repositories\Category;

use App\Models\Category;
use App\Repositories\BaseRepository;

class CategoryRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Category::class;
    }

    // find categories by store id and those without store id
    public function findByStoreIdOrNull(int $storeId)
    {
        return $this->getQueryBuilder()->where(function ($query) use ($storeId) {
            $query->where(Category::COL_STORE_ID, $storeId)
                  ->orWhereNull(Category::COL_STORE_ID);
        })->get();
    }
}