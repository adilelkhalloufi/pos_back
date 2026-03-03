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
        Schema::create('ajustement_items', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('ajustement_id')->constrained('ajustements')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            
            // Adjustment type and quantity
            $table->enum('type', ['increase', 'decrease'])->default('increase');
            $table->decimal('quantity', 12, 4);
            $table->decimal('previous_stock', 14, 4)->nullable();
            $table->decimal('new_stock', 14, 4)->nullable();
            
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
        Schema::dropIfExists('ajustement_items');
    }
};
