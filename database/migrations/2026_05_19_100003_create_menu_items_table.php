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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_category_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('cost', 15, 4)->default(0)->comment('Calculated from recipe cost');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true)->comment('Temporary availability (stock-based)');
            $table->integer('preparation_time_minutes')->nullable();
            $table->enum('item_type', ['recipe', 'combo', 'simple'])->default('recipe')->comment('Link type');
            $table->foreignId('recipe_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->integer('display_order')->default(0);
            $table->timestamps();

            // Indexes
            $table->index(['menu_category_id', 'is_active']);
            $table->index('recipe_id');
            $table->index(['is_active', 'is_available', 'store_id']);
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
