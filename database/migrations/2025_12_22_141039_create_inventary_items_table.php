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
        Schema::create('inventary_items', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('inventary_id')->constrained('inventaries')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            
            // Stock data
            $table->decimal('expected_quantity', 12, 4);
            $table->decimal('actual_quantity', 12, 4)->nullable();
            $table->decimal('difference', 12, 4)->default(0);
            
            // Status
            $table->enum('status', ['pending', 'checked', 'discrepancy'])->default('pending');
            
            // Additional info
            $table->text('note')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventary_items');
    }
};
