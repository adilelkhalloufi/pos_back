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
        Schema::table('stock_movements', function (Blueprint $table) {
            // Add weighted average cost tracking
            $table->decimal('average_cost_before', 15, 4)->nullable()->after('total_cost')
                ->comment('Weighted average cost before this movement');
            $table->decimal('average_cost_after', 15, 4)->nullable()->after('average_cost_before')
                ->comment('Weighted average cost after this movement');
            $table->decimal('total_value_before', 20, 4)->nullable()->after('average_cost_after')
                ->comment('Total inventory value before movement');
            $table->decimal('total_value_after', 20, 4)->nullable()->after('total_value_before')
                ->comment('Total inventory value after movement');

            // Add index for cost analysis queries
            $table->index(['product_id', 'created_at', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'created_at', 'type']);
            $table->dropColumn([
                'average_cost_before',
                'average_cost_after',
                'total_value_before',
                'total_value_after'
            ]);
        });
    }
};
