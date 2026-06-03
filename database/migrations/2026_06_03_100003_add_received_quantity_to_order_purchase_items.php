<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_purchase_items', function (Blueprint $table) {
            // Add new columns for tracking received quantities
            $table->integer('received_quantity')->default(0)->after('quantity');
            $table->integer('remaining_quantity')->default(0)->after('received_quantity');
        });

        // Update existing records: set remaining_quantity = quantity for existing orders
        DB::statement('UPDATE order_purchase_items SET remaining_quantity = quantity WHERE remaining_quantity = 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_purchase_items', function (Blueprint $table) {
            $table->dropColumn(['received_quantity', 'remaining_quantity']);
        });
    }
};
