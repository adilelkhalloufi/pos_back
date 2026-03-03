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
        Schema::create('transfert_items', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('transfert_id')->constrained('transferts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products');
            
            // Quantity
            $table->decimal('quantity', 12, 4);
            
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
        Schema::dropIfExists('transfert_items');
    }
};
