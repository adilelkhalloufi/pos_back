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

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('cin')->nullable();
            $table->string('name')->nullable();
            $table->string('adress')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->date('birthday')->nullable();
            $table->boolean('gender')->nullable()->comment('0: female, 1: male')->default(1);
            $table->integer('status')->nullable()->comment('1:active, 2:inactive, 3:bankrupt');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users');

            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
