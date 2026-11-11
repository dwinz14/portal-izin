<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            // 0 = tidak ada aturan mendadak. Default 7 (H-7)
            $table->unsignedSmallInteger('advance_notice_days')->default(7)->after('min_years');
        });


        DB::table('leave_types')
            ->whereRaw('LOWER(name) IN (?, ?)', [
                'izin sakit dengan surat dokter',
                'izin sakit tanpa surat dokter',
            ])
            ->update(['advance_notice_days' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropColumn('advance_notice_days');
        });
    }
};
