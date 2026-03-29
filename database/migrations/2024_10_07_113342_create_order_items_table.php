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

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->morphs('product');

            $table->foreignId('order_id')
                ->nullable()
                ->constrained('order_sales');

            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores');

            $table->string('name')->nullable();
            $table->integer('qte')->nullable();
            $table->float('price')->nullable();
            $table->float('invoice_price')->nullable();
            $table->float('discount')->nullable();
            $table->float('total')->nullable();
            $table->foreignId('category_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
