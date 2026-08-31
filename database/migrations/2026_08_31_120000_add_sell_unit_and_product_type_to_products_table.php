<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_type')->default('normal')->after('is_stockable');
            $table->foreignId('sell_unit_id')->nullable()->after('unit_id')->constrained('units')->nullOnDelete();
            $table->index('product_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['product_type']);
            $table->dropForeign(['sell_unit_id']);
            $table->dropColumn('sell_unit_id');
            $table->dropColumn('product_type');
        });
    }
};
