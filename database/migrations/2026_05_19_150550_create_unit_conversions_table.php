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
        Schema::create('unit_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('to_unit_id')->constrained('units')->onDelete('cascade');
            $table->decimal('conversion_factor', 16, 6)->comment('Multiplier to convert from_unit to to_unit (e.g., 1 KG = 1000 Gram, factor: 1000)');
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->timestamps();

            // Unique constraint to prevent duplicate conversions
            $table->unique(['from_unit_id', 'to_unit_id', 'store_id'], 'unique_unit_conversion');
            
            // Indexes for performance
            $table->index(['from_unit_id', 'to_unit_id']);
            $table->index('store_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_conversions');
    }
};
