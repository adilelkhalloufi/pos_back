<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('price_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('field')->comment('price_buy|price_sell_1|price_sell_2');
            $table->decimal('old_value', 10, 4)->nullable();
            $table->decimal('new_value', 10, 4);
            $table->date('effective_date')->nullable();
            $table->string('reason')->nullable();
            $table->foreignId('store_id')->nullable()->constrained('stores');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_change_logs');
    }
};
