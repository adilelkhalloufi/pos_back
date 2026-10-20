<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('storefront_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->boolean('is_published')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('short_description')->nullable();
            $table->string('cover_image')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'product_id']);
            $table->index(['store_id', 'is_published']);
        });

        Schema::create('storefront_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('order_number');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('currency', 8)->default('MAD');
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();

            $table->index(['store_id', 'created_at']);
            $table->index(['store_id', 'status']);
            $table->index('order_number');
        });

        Schema::create('storefront_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('storefront_orders')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('variant_name')->nullable();
            $table->string('sku')->nullable();
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('material')->nullable();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storefront_order_items');
        Schema::dropIfExists('storefront_orders');
        Schema::dropIfExists('storefront_products');
    }
};
