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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100); // "Breakfast Menu", "Dinner Menu"
            $table->text('description')->nullable();
            $table->enum('type', ['breakfast', 'lunch', 'dinner', 'drinks', 'all_day'])->default('all_day');
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->time('available_from_time')->nullable()->comment('Menu available from time (e.g., 07:00)');
            $table->time('available_to_time')->nullable()->comment('Menu available to time (e.g., 11:00)');
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index(['is_active', 'store_id']);
            $table->index(['type', 'is_active']);
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
