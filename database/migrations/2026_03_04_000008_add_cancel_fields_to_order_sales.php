<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::table('order_sales', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('is_invoice');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->after('cancelled_at');
            $table->string('cancel_reason')->nullable()->after('cancelled_by');
        });
    }

    public function down(): void
    {
        Schema::table('order_sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by');
            $table->dropColumn(['cancelled_at', 'cancel_reason']);
        });
    }
};
