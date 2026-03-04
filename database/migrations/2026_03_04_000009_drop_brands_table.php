<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        // Remove brand_id foreign key + column from products if present
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'brand_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropConstrainedForeignId('brand_id');
            });
        }

        // Drop brands table — not needed in this application
        Schema::dropIfExists('brands');
    }

    public function down(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('store_id')->nullable()->constrained('stores');
            $table->timestamps();
        });
    }
};
