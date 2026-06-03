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
        Schema::create('purchase_delivery_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('purchase_delivery_id')
                ->constrained('purchase_deliveries')
                ->onDelete('cascade');

            $table->foreignId('order_purchase_item_id')
                ->constrained('order_purchase_items')
                ->onDelete('cascade');

            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            $table->integer('ordered_quantity');      // Reference from BC
            $table->integer('delivered_quantity');    // Actually received
            $table->integer('accepted_quantity');     // After quality check
            $table->integer('rejected_quantity')->default(0);

            $table->decimal('unit_price', 20, 2);
            $table->decimal('total_price', 20, 2);

            $table->text('rejection_reason')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_delivery_items');
    }
};
