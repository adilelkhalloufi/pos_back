<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AppliesDateFilters
{
    /**
     * Apply date range filter to a query if parameters exist, otherwise limit to latest records.
     *
     * @param Builder $query
     * @param Request $request
     * @param int $defaultLimit Default number of records when no filter is applied
     * @param string $dateColumn The column to filter on (default: 'created_at')
     * @return Builder
     */
    protected function applyDateFilter(
        Builder $query,
        Request $request,
        int $defaultLimit = 25,
        string $dateColumn = 'created_at'
    ): Builder {
        $dateStart = $request->input('date_start');
        $dateEnd = $request->input('date_end');

        // If date filters are provided, apply them
        if ($dateStart || $dateEnd) {
            return $query->dateRange($dateStart, $dateEnd, $dateColumn);
        }

        // Otherwise, return the latest records with limit
        return $query->latestRecords($defaultLimit);
    }

    /**
     * Check if the request has date filters.
     *
     * @param Request $request
     * @return bool
     */
    protected function hasDateFilter(Request $request): bool
    {
        return $request->has('date_start') || $request->has('date_end');
    }
}
