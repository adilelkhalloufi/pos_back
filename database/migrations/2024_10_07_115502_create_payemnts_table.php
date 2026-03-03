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
        Schema::create('payemnts', function (Blueprint $table) {
            $table->id();
            
            $table->decimal('amount')->default(0);
            $table->integer('status')->default(2)->comment('1: unpaid, 2: avance, 3: paid');

            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores');


            $table->text('note')->nullable();

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('order_sales');
            
            $table->foreignId('order_purchase_id')
                ->nullable()
                ->constrained('order_purchases');

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers');

            $table->foreignId('mode_payemnt_id')
                ->nullable()
                ->constrained('mode_payemnts');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payemnts');
    }
};
