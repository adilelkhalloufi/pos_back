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
        Schema::create('transferts', function (Blueprint $table) {
            $table->id();
            
            // Reference number
            $table->string('reference')->unique();
            $table->foreignId('store_id')->constrained('stores');
            // Source and target stores
            $table->foreignId('source_store_id')->constrained('stores');
            $table->foreignId('target_store_id')->constrained('stores');
            
            // Status
            $table->enum('status', ['pending', 'in_transit', 'completed', 'cancelled'])->default('pending');
            
            // Users involved
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Dates
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            
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
        Schema::dropIfExists('transferts');
    }
};
