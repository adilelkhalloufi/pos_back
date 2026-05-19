<?php

namespace App\Services\UnitConversion;

use App\Models\UnitConversion;
use App\Models\Unit;
use Exception;
use Illuminate\Support\Facades\Cache;

/**
 * ConversionService - Handles unit conversions for stock deduction
 */
class ConversionService
{
    /**
     * Convert quantity from one unit to another
     * 
     * @param float $quantity - Quantity to convert
     * @param int $fromUnitId - Source unit ID
     * @param int $toUnitId - Target unit ID
     * @param int|null $storeId - Store ID for store-specific conversions (null for global)
     * @return float - Converted quantity
     * @throws Exception if no conversion found
     */
    public function convert(float $quantity, int $fromUnitId, int $toUnitId, ?int $storeId = null): float
    {
        // If units are the same, no conversion needed
        if ($fromUnitId === $toUnitId) {
            return $quantity;
        }

        // Try to find direct conversion
        $conversion = $this->findConversion($fromUnitId, $toUnitId, $storeId);

        if ($conversion) {
            return $quantity * $conversion->conversion_factor;
        }

        // Try reverse conversion (to_unit → from_unit)
        $reverseConversion = $this->findConversion($toUnitId, $fromUnitId, $storeId);

        if ($reverseConversion) {
            return $quantity / $reverseConversion->conversion_factor;
        }

        // No direct or reverse conversion found
        throw new Exception("No conversion found from unit ID $fromUnitId to unit ID $toUnitId");
    }

    /**
     * Find a conversion rule
     * 
     * @param int $fromUnitId
     * @param int $toUnitId
     * @param int|null $storeId
     * @return UnitConversion|null
     */
    protected function findConversion(int $fromUnitId, int $toUnitId, ?int $storeId = null): ?UnitConversion
    {
        $cacheKey = "unit_conversion_{$fromUnitId}_{$toUnitId}_" . ($storeId ?? 'global');

        return Cache::remember($cacheKey, 3600, function () use ($fromUnitId, $toUnitId, $storeId) {
            $query = UnitConversion::where('from_unit_id', $fromUnitId)
                ->where('to_unit_id', $toUnitId);

            // First try store-specific conversion
            if ($storeId) {
                $storeConversion = (clone $query)->where('store_id', $storeId)->first();
                if ($storeConversion) {
                    return $storeConversion;
                }
            }

            // Fall back to global conversion (store_id is null)
            return $query->whereNull('store_id')->first();
        });
    }

    /**
     * Create or update a conversion rule
     * 
     * @param int $fromUnitId
     * @param int $toUnitId
     * @param float $conversionFactor
     * @param int|null $storeId
     * @return UnitConversion
     */
    public function createConversion(int $fromUnitId, int $toUnitId, float $conversionFactor, ?int $storeId = null): UnitConversion
    {
        // Validate units exist
        Unit::findOrFail($fromUnitId);
        Unit::findOrFail($toUnitId);

        // Create or update
        $conversion = UnitConversion::updateOrCreate(
            [
                'from_unit_id' => $fromUnitId,
                'to_unit_id' => $toUnitId,
                'store_id' => $storeId,
            ],
            [
                'conversion_factor' => $conversionFactor,
            ]
        );

        // Clear cache
        $cacheKey = "unit_conversion_{$fromUnitId}_{$toUnitId}_" . ($storeId ?? 'global');
        Cache::forget($cacheKey);

        return $conversion;
    }

    /**
     * Delete a conversion rule
     * 
     * @param int $conversionId
     * @return bool
     */
    public function deleteConversion(int $conversionId): bool
    {
        $conversion = UnitConversion::findOrFail($conversionId);
        
        // Clear cache
        $cacheKey = "unit_conversion_{$conversion->from_unit_id}_{$conversion->to_unit_id}_" . ($conversion->store_id ?? 'global');
        Cache::forget($cacheKey);

        return $conversion->delete();
    }

    /**
     * Get all conversions for a store
     * 
     * @param int|null $storeId - null for global conversions
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getConversions(?int $storeId = null)
    {
        $query = UnitConversion::with(['fromUnit', 'toUnit', 'store']);

        if ($storeId === null) {
            return $query->whereNull('store_id')->get();
        }

        return $query->where('store_id', $storeId)->get();
    }

    /**
     * Check if conversion is possible
     * 
     * @param int $fromUnitId
     * @param int $toUnitId
     * @param int|null $storeId
     * @return bool
     */
    public function canConvert(int $fromUnitId, int $toUnitId, ?int $storeId = null): bool
    {
        if ($fromUnitId === $toUnitId) {
            return true;
        }

        return $this->findConversion($fromUnitId, $toUnitId, $storeId) !== null
            || $this->findConversion($toUnitId, $fromUnitId, $storeId) !== null;
    }

    /**
     * Create standard conversions for common units
     * This can be called during seeding or setup
     * 
     * @param int|null $storeId
     * @return array
     */
    public function createStandardConversions(?int $storeId = null): array
    {
        $conversions = [];

        // Weight conversions
        $kg = Unit::where('symbol', 'kg')->orWhere('name', 'Kilogram')->first();
        $g = Unit::where('symbol', 'g')->orWhere('name', 'Gram')->first();
        $mg = Unit::where('symbol', 'mg')->orWhere('name', 'Milligram')->first();

        if ($kg && $g) {
            $conversions[] = $this->createConversion($kg->id, $g->id, 1000, $storeId);
        }

        if ($g && $mg) {
            $conversions[] = $this->createConversion($g->id, $mg->id, 1000, $storeId);
        }

        // Volume conversions
        $l = Unit::where('symbol', 'L')->orWhere('name', 'Liter')->first();
        $ml = Unit::where('symbol', 'ml')->orWhere('name', 'Milliliter')->first();

        if ($l && $ml) {
            $conversions[] = $this->createConversion($l->id, $ml->id, 1000, $storeId);
        }

        return $conversions;
    }
}
