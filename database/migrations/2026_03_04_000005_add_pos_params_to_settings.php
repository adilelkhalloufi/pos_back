<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Printing
            $table->unsignedSmallInteger('max_print_copies')->default(1)->after('currency');

            // Secondary display (affichage secondaire)
            $table->boolean('secondary_display_enabled')->default(false)->after('max_print_copies');
            $table->string('secondary_display_connection')->default('com')->nullable()
                ->comment('com|network')->after('secondary_display_enabled');
            $table->string('secondary_display_com_port')->nullable()->after('secondary_display_connection');
            $table->unsignedSmallInteger('secondary_display_x')->default(0)->after('secondary_display_com_port');
            $table->unsignedSmallInteger('secondary_display_y')->default(0)->after('secondary_display_x');
            $table->unsignedSmallInteger('secondary_display_width')->default(800)->after('secondary_display_y');
            $table->unsignedSmallInteger('secondary_display_height')->default(600)->after('secondary_display_width');

            // Passport reader
            $table->boolean('passport_reader_enabled')->default(false)->after('secondary_display_height');
            $table->string('passport_reader_com_port')->nullable()->after('passport_reader_enabled');
            $table->unsignedSmallInteger('passport_reader_baud_rate')->default(9600)->after('passport_reader_com_port');
            $table->string('passport_reader_provider')->nullable()->after('passport_reader_baud_rate');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'max_print_copies',
                'secondary_display_enabled',
                'secondary_display_connection',
                'secondary_display_com_port',
                'secondary_display_x',
                'secondary_display_y',
                'secondary_display_width',
                'secondary_display_height',
                'passport_reader_enabled',
                'passport_reader_com_port',
                'passport_reader_baud_rate',
                'passport_reader_provider',
            ]);
        });
    }
};
