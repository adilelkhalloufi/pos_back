<?php

namespace App\Repositories\Ajustement;

use App\Models\Ajustement;
use App\Repositories\BaseRepository;

class AjustementRepository extends BaseRepository
{
    public function getModelClass(): string
    {
        return Ajustement::class;
    }

    /**
     * Get adjustments for a specific store
     * 
     * @param int $storeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByStore(int $storeId)
    {
        return $this->getQueryBuilder()
            ->where(Ajustement::COL_STORE_ID, $storeId)
            ->with(['store', 'items.product', 'user','targetStore'])
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get adjustments by type
     * 
     * @param string $type
     * @param int|null $storeId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByType(string $type, ?int $storeId = null)
    {
        $query = $this->getQueryBuilder()
            ->with(['store', 'items.product', 'user']);

        if ($storeId) {
            $query->where(Ajustement::COL_STORE_ID, $storeId);
        }

        return $query->orderBy('id', 'desc')->get();
    }
}
