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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();

            // Alert type and category
            $table->string('type'); // customer_inactive, product_low_stock, product_out_of_stock, staff_alert, etc.
            $table->string('category')->default('general'); // customer, product, staff, system

            // Alert content
            $table->string('title');
            $table->text('message');
            $table->string('severity')->default('medium'); // low, medium, high, critical

            // Status tracking
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');

            // Related entities (nullable because different alert types relate to different entities)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignId('store_id')->nullable()->constrained('stores')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade'); // for staff alerts

            // Additional data
            $table->json('metadata')->nullable(); // Store additional context like thresholds, dates, etc.
            $table->timestamp('triggered_at')->nullable(); // When the alert condition was met

            $table->timestamps();

            // Indexes for performance
            $table->index(['type', 'is_resolved']);
            $table->index(['category', 'is_read']);
            $table->index(['store_id', 'is_resolved']);
            $table->index(['customer_id', 'type']);
            $table->index(['product_id', 'type']);
            $table->index(['user_id', 'type']);
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
