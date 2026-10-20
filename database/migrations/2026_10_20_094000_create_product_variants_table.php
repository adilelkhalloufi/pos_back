<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('sku')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('material')->nullable();
            $table->decimal('weight_grams', 10, 3)->nullable();
            $table->string('gemstone')->nullable();
            $table->json('attributes')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('stock', 12, 3)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['store_id', 'product_id']);
            $table->index(['store_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
