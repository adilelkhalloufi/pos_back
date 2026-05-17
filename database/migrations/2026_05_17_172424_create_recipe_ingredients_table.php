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
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade')->comment('Ingredient from products table');
            $table->decimal('quantity', 12, 4); // Quantity needed
            $table->foreignId('unit_id')->constrained()->onDelete('cascade'); // Unit of measurement
            $table->decimal('waste_percentage', 5, 2)->default(0)->comment('Trim/prep loss percentage');
            $table->text('preparation_note')->nullable()->comment('Prep instructions for this ingredient');
            $table->boolean('is_optional')->default(false);
            $table->decimal('cost', 15, 4)->default(0)->comment('Calculated cost (quantity * unit_cost)');
            $table->timestamps();

            // Unique constraint: one ingredient per recipe
            $table->unique(['recipe_id', 'product_id']);

            // Indexes for performance
            $table->index('recipe_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
