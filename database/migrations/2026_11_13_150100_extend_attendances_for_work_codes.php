<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->string('attendance_code', 10)->nullable()->after('date');
            $table->decimal('work_units', 5, 2)->default(0)->after('status');
        });

        DB::table('attendances')
            ->whereNull('attendance_code')
            ->update([
                'attendance_code' => DB::raw("CASE WHEN is_present = 1 OR status = 'present' THEN 'P' ELSE 'A' END"),
                'work_units' => DB::raw("CASE WHEN is_present = 1 OR status = 'present' THEN 1 ELSE 0 END"),
            ]);
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn(['attendance_code', 'work_units']);
        });
    }
};

