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
        Schema::create('ajustements', function (Blueprint $table) {
            $table->id();
            
            // Reference number
            $table->string('reference')->unique();
            
            // Store
            $table->foreignId('store_id')->constrained('stores')->comment('Store where the adjustment is made');
            $table->foreignId('target_store_id')->nullable()->constrained('stores');
            
            // Reason
            $table->enum('reason', ['damaged', 'lost', 'found', 'expired', 'correction', 'other'])->default('other');
            $table->text('note')->nullable();
            
            // User who made the adjustment
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'in_transit', 'completed', 'cancelled'])->default('pending');

            // Additional info
            $table->json('meta')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustements');
    }
};
