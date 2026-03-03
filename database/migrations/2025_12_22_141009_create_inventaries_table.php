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
        Schema::create('inventaries', function (Blueprint $table) {
            $table->id();
            
            // Reference number
            $table->string('reference')->unique();
            
            // Store
            $table->foreignId('store_id')->constrained('stores');

            $table->foreignId('target_store_id')->nullable()->constrained('stores');
            
            // Inventory status and dates
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Users involved
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Summary data
            $table->integer('total_items')->default(0);
            $table->integer('checked_items')->default(0);
            $table->decimal('total_difference', 14, 4)->default(0);
            
            // Additional info
            $table->text('note')->nullable();
            $table->json('meta')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaries');
    }
};
