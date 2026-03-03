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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('website')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('city')->nullable();
            $table->string('if')->nullable();
            $table->string('ice')->nullable();
            $table->string('rc')->nullable();
            $table->string('patente')->nullable();
            $table->string('cnss')->nullable();
            $table->string('tax')->nullable();

            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
