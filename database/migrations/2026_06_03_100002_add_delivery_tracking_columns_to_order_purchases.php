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
        Schema::table('order_purchases', function (Blueprint $table) {
            $table->string('delivery_status')->default('not_started')
                ->after('status')
                ->comment('not_started, in_progress, partially_received, fully_received');

            $table->date('ordered_date')->nullable()->after('delivery_status');
            $table->date('expected_delivery_date')->nullable()->after('ordered_date');
            $table->date('first_delivery_date')->nullable()->after('expected_delivery_date');
            $table->date('last_delivery_date')->nullable()->after('first_delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_purchases', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_status',
                'ordered_date',
                'expected_delivery_date',
                'first_delivery_date',
                'last_delivery_date'
            ]);
        });
    }
};
