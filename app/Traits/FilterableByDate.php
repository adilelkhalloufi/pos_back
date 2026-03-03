<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait FilterableByDate
{
    /**
     * Scope a query to filter by date range.
     *
     * @param Builder $query
     * @param string|null $dateStart
     * @param string|null $dateEnd
     * @param string $dateColumn The column to filter on (default: 'created_at')
     * @return Builder
     */
    public function scopeDateRange(Builder $query, ?string $dateStart, ?string $dateEnd, string $dateColumn = 'created_at'): Builder
    {
        if ($dateStart && $dateEnd) {
            // Parse dates and set time boundaries to include entire day
            $startDate = Carbon::parse($dateStart)->startOfDay();
            $endDate = Carbon::parse($dateEnd)->endOfDay();

            return $query->whereBetween($dateColumn, [$startDate, $endDate]);
        }

        if ($dateStart) {
            $startDate = Carbon::parse($dateStart)->startOfDay();
            return $query->where($dateColumn, '>=', $startDate);
        }

        if ($dateEnd) {
            $endDate = Carbon::parse($dateEnd)->endOfDay();
            return $query->where($dateColumn, '<=', $endDate);
        }

        return $query;
    }

    /**
     * Scope a query to get the latest records with a limit.
     *
     * @param Builder $query
     * @param int $limit
     * @param string $orderColumn
     * @return Builder
     */
    public function scopeLatestRecords(Builder $query, int $limit = 25, string $orderColumn = 'id'): Builder
    {
        return $query->orderByDesc($orderColumn)->limit($limit);
    }
}
