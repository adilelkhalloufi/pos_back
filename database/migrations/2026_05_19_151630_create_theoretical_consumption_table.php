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
        Schema::create('theoretical_consumption', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->date('date')->comment('Consumption date');
            $table->decimal('theoretical_quantity', 14, 4)->default(0)->comment('Sum of recipe usage × sales quantity');
            $table->decimal('actual_quantity', 14, 4)->default(0)->comment('From stock movements');
            $table->decimal('variance', 14, 4)->default(0)->comment('actual - theoretical');
            $table->decimal('variance_percentage', 5, 2)->default(0)->comment('(variance/theoretical) × 100');
            $table->timestamps();

            // Unique constraint
            $table->unique(['product_id', 'store_id', 'date'], 'unique_consumption_record');
            
            // Indexes for performance
            $table->index(['store_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('theoretical_consumption');
    }
};
