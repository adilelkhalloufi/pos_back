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
        Schema::table('customers', function (Blueprint $table) {

            $table->integer('total_orders')->default(0)->comment('Total number of orders');
            $table->decimal('total_amount_orders', 10, 2)->default(0)->comment('Total amount of orders');
            $table->decimal('total_payments', 10, 2)->default(0)->comment('Total paid amount');
            $table->integer('total_prescriptions')->default(0)->comment('Total number of prescriptions');
            $table->date('last_order_date')->nullable()->comment('Date of the last order');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            //
        });
    }
};
