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
        Schema::table('order_purchase_items', function (Blueprint $table) {
            $table->decimal('price_ht', 20, 2)->default(0);
            $table->decimal('tva', 20, 2)->default(0);
            $table->decimal('price_ttc', 20, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_purchase_items', function (Blueprint $table) {
            $table->dropColumn(['price_ht', 'tva', 'price_ttc']);
        });
    }
};
