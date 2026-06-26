<?php

namespace App\Console\Commands;

use App\Models\Unit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteUnusedUnits extends Command
{
    protected $signature = 'units:delete-unused
        {--dry-run : Show unused units without deleting them}
        {--force : Delete without asking for confirmation}';

    protected $description = 'Delete units that are not referenced by products, recipe ingredients, or recipes.';

    public function handle(): int
    {
        $usedUnitIds = collect()
            ->merge(DB::table('products')->whereNotNull('unit_id')->pluck('unit_id'))
            ->merge(DB::table('product_components')->whereNotNull('unit_id')->pluck('unit_id'))
            ->merge(DB::table('recipe_ingredients')->whereNotNull('unit_id')->pluck('unit_id'))
            ->merge(DB::table('recipes')->whereNotNull('yield_unit_id')->pluck('yield_unit_id'))
            ->filter()
            ->unique()
            ->values();

        $unusedUnits = Unit::whereNotIn(Unit::COL_ID, $usedUnitIds)->get();

        if ($unusedUnits->isEmpty()) {
            $this->info('No unused units found.');
            return self::SUCCESS;
        }

        $this->info('Found ' . $unusedUnits->count() . ' unused unit(s):');
        foreach ($unusedUnits as $unit) {
            $symbol = $unit->symbol ? " ({$unit->symbol})" : '';
            $this->line("  - {$unit->id}: {$unit->name}{$symbol}");
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run complete. No units were deleted.');
            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('Delete these unused units from the database?')) {
            $this->comment('Action cancelled. No units were deleted.');
            return self::SUCCESS;
        }

        $deleted = Unit::whereIn(Unit::COL_ID, $unusedUnits->pluck(Unit::COL_ID))->delete();

        $this->info("Deleted {$deleted} unused unit(s).");
        return self::SUCCESS;
    }
}
