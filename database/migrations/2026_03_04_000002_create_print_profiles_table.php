<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('print_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('printer_name')->nullable()->comment('System printer name or IP:port');
            $table->string('connection_type')->default('usb')->comment('usb|network|com');
            $table->string('com_port')->nullable();
            $table->unsignedSmallInteger('max_copies')->default(1);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('store_id')->nullable()->constrained('stores');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_profiles');
    }
};
