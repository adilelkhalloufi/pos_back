<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // The product affected by the movement
            $table->foreignId('product_id')->constrained('products');

            // Source store where the movement originates
            $table->foreignId('source_store_id')->nullable()->constrained('stores');

            // Target store for transfers (nullable for non-transfer movements)
            $table->foreignId('target_store_id')->nullable()->constrained('stores');

            // Store performing the movement action
            $table->foreignId('store_id')->constrained('stores');

            // Movement type and direction
            $table->enum('type', ['sale', 'purchase', 'transfer', 'adjustment', 'inventory'])->default('adjustment');
            $table->enum('direction', ['in', 'out'])->default('out');

            // Quantities & costs
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_cost', 15, 4)->nullable();
            $table->decimal('total_cost', 20, 4)->nullable();

            // Snapshot of stock before/after movement (optional)
            $table->decimal('previous_stock', 14, 4)->nullable();
            $table->decimal('new_stock', 14, 4)->nullable();

            // Reference to the related entity (order, purchase, transfer record, adjustment, etc.)
            $table->nullableMorphs('referenceable');

            // Who performed and who approved (optional)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Extra information 
            $table->text('note')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void
    {

        Schema::dropIfExists('stock_movements');
    }
};
