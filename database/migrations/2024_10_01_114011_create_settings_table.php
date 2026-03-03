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

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('license_key')->nullable();
            $table->boolean('is_license_expired')->default(false);
            $table->date('license_expiry_date')->nullable();
            $table->integer('invoice_sequence')->default(0);
            $table->integer('purchase_sequence')->default(0);
            $table->integer('sale_sequence')->default(0);
            $table->string('order_prefix')->default("OR")->nullable();
            $table->string('invoice_prefix')->default("FAC")->nullable();
            $table->string('purchase_prefix')->default("PA")->nullable();
            $table->string('currency')->default("USD")->nullable();


            $table->text('document_header')->nullable();
            $table->text('document_footer')->nullable();
            $table->string('company_name')->nullable();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users');
            $table->foreignId('store_id')
                ->nullable()
                ->constrained('stores');

            // 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
