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

        Schema::create('order_sales', function (Blueprint $table) {
            $table->id();

            $table->string('order_number')->nullable();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('customers');

            $table->integer('status')->nullable()->comment('1: unpaid, 2: avance, 3: paid');
            $table->decimal('discount')->nullable();
            $table->decimal('advance')->nullable();
            $table->boolean('is_invoice')->nullable();

            $table->decimal('total_command')->nullable();
            $table->decimal('total_payment')->nullable();
            $table->decimal('rest_a_pay')->nullable();

            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores');

            $table->foreignId('paid_method_id')
                ->nullable()
                ->constrained('mode_payemnts');

            $table->text('note')->nullable();



            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_sales');
    }
};
