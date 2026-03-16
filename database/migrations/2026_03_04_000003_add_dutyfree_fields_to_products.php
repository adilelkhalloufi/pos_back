<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('supplier_code')->nullable()->after('reference')->comment('Code fournisseur');
            $table->decimal('price_sell_1', 10, 4)->nullable()->after('price_buy')->comment('PV1 — main selling price');
            $table->boolean('is_stockable')->default(true)->after('is_active');
            $table->foreignId('unit_id')->nullable()->after('category_id')->constrained('units');
            $table->foreignId('print_profile_id')->nullable()->after('unit_id')->constrained('print_profiles');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropConstrainedForeignId('print_profile_id');
            $table->dropColumn(['supplier_code', 'price_sell_1', 'is_stockable']);
        });
    }
};
