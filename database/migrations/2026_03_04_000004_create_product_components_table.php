<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()
                ->comment('Parent / composed product');
            $table->foreignId('component_id')->constrained('products')->cascadeOnDelete()
                ->comment('Raw or sub product');
            $table->decimal('quantity', 10, 4)->default(1);
            $table->foreignId('unit_id')->nullable()->constrained('units');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_components');
    }
};
