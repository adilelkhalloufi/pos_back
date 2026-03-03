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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products');

            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices');
            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores');
            $table->string('label')->nullable();
            $table->integer('quantity')->nullable();
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
        Schema::dropIfExists('invoice_items');
    }
};
