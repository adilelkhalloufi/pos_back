<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('reference')->nullable();
            $table->string('codebar')->nullable()->index();

            $table->string('slug')->nullable();
            $table->string('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price')->nullable();

            $table->decimal('stock_alert')->nullable();
             $table->boolean('is_active')->default(false);
           

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
