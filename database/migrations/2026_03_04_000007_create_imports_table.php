<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('products|prices');
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending')->comment('pending|validated|committed|failed');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('valid_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->unsignedInteger('committed_rows')->default(0);
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->foreignId('store_id')->nullable()->constrained('stores');
            $table->timestamps();
        });

        Schema::create('import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained('imports')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('raw_data');
            $table->json('errors')->nullable();
            $table->string('status')->default('pending')->comment('pending|valid|error|committed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_rows');
        Schema::dropIfExists('imports');
    }
};
