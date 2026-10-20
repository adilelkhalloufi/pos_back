<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->string('public_slug')->nullable()->unique()->after('website');
            $table->boolean('storefront_enabled')->default(false)->after('public_slug');
            $table->string('storefront_title')->nullable()->after('storefront_enabled');
            $table->text('storefront_description')->nullable()->after('storefront_title');
            $table->string('storefront_banner')->nullable()->after('storefront_description');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn([
                'storefront_banner',
                'storefront_description',
                'storefront_title',
                'storefront_enabled',
                'public_slug',
            ]);
        });
    }
};
