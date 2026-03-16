<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('supplier_code')->nullable()->comment('Code fournisseur');
            $table->decimal('price_sell_1', 10, 4)->nullable()->comment('PV1 — main selling price');
            $table->decimal('price_buy', 10, 4)->nullable()->comment('PA — purchase price');
            $table->boolean('is_stockable')->default(true);
            $table->foreignId('unit_id')->nullable();
            $table->foreignId('print_profile_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropConstrainedForeignId('print_profile_id');
         });
    }
};
