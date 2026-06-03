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
        Schema::create('purchase_deliveries', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_number')->unique(); // BL-0001

            $table->foreignId('order_purchase_id')
                ->constrained('order_purchases')
                ->onDelete('cascade');

            $table->foreignId('store_id')
                ->constrained('stores')
                ->onDelete('cascade');

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->onDelete('cascade');

            $table->foreignId('received_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->date('delivery_date');
            $table->string('supplier_delivery_note')->nullable(); // Supplier's BL number
            $table->string('transport_company')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('vehicle_plate')->nullable();

            $table->text('delivery_note')->nullable();
            $table->text('quality_check_note')->nullable();
            $table->boolean('has_issues')->default(false);

            $table->enum('status', ['draft', 'validated', 'cancelled'])->default('draft');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_deliveries');
    }
};
