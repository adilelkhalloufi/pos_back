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
        // public const COL_LABEL = 'label';

        // public const COL_QUANTITY = 'quantity';
        // public const COL_PRICE = 'price';
        // public const COL_INVOICE_PRICE = 'invoice_price';

        // public const COL_DISCOUNT = 'discount';
        // public const COL_TOTAL = 'total';
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
