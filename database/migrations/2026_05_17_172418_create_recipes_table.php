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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->decimal('yield_quantity', 12, 4)->default(1); // Recipe yield (e.g., makes 4 servings)
            $table->foreignId('yield_unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->integer('preparation_time_minutes')->nullable(); // Prep time
            $table->integer('cooking_time_minutes')->nullable(); // Cook time
            $table->string('skill_level')->nullable(); // easy, medium, hard
            $table->decimal('total_cost', 15, 4)->default(0)->comment('Calculated total cost of recipe');
            $table->decimal('cost_per_serving', 15, 4)->default(0)->comment('Cost divided by yield');
            $table->integer('version')->default(1); // Recipe version for future tracking
            $table->boolean('is_active')->default(true);
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index(['is_active', 'store_id']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
