<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_requests', function (Blueprint $table) {
            $table->enum('update_type', ['checkin_only', 'checkout_only', 'both'])
                ->nullable()
                ->after('type')
                ->comment('Hanya diisi ketika type = update_attendance');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_requests', function (Blueprint $table) {
            $table->dropColumn('update_type');
        });
    }
};
