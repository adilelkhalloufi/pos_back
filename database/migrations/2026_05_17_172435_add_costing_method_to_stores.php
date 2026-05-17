<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            // Add inventory costing method configuration
            $table->enum('costing_method', ['weighted_average', 'fifo', 'simple_average'])
                ->default('weighted_average')
                ->after('owner_id')
                ->comment('Inventory valuation method: weighted_average (recommended for restaurants), fifo, or simple_average');

            // Add food cost target percentage for monitoring
            $table->decimal('target_food_cost_percentage', 5, 2)->nullable()->after('costing_method')
                ->comment('Target food cost percentage for alerts (e.g., 30.00 = 30%)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn(['costing_method', 'target_food_cost_percentage']);
        });
    }
};
