<?php

namespace App\Services\Unit;

use App\Models\Unit;

class UnitService
{
    public function all(int $storeId)
    {
        return Unit::where(Unit::COL_STORE_ID, $storeId)
            ->orWhereNull(Unit::COL_STORE_ID)
            ->orderBy(Unit::COL_NAME)
            ->get();
    }

    public function create(array $attributes): Unit
    {
        return Unit::create([
            Unit::COL_NAME        => $attributes[Unit::COL_NAME],
            Unit::COL_SYMBOL      => $attributes[Unit::COL_SYMBOL] ?? null,
            Unit::COL_DESCRIPTION => $attributes[Unit::COL_DESCRIPTION] ?? null,
            Unit::COL_IS_ACTIVE   => $attributes[Unit::COL_IS_ACTIVE] ?? true,
            Unit::COL_STORE_ID    => currentStoreId(),
        ]);
    }

    public function update(int $id, array $attributes): Unit
    {
        $unit = Unit::findOrFail($id);
        $unit->update([
            Unit::COL_NAME        => $attributes[Unit::COL_NAME] ?? $unit->name,
            Unit::COL_SYMBOL      => $attributes[Unit::COL_SYMBOL] ?? $unit->symbol,
            Unit::COL_DESCRIPTION => $attributes[Unit::COL_DESCRIPTION] ?? $unit->description,
            Unit::COL_IS_ACTIVE   => $attributes[Unit::COL_IS_ACTIVE] ?? $unit->is_active,
        ]);
        return $unit->fresh();
    }

    public function delete(int $id): void
    {
        Unit::findOrFail($id)->delete();
    }
}
