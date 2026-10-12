<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_leave_balances', function (Blueprint $table) {
            // Composite index untuk query EXISTS saat login
            // WHERE user_id = ? AND year = ?
            $table->index(['user_id', 'year'], 'idx_user_leave_balances_user_year');
        });
    }

    public function down(): void
    {
        Schema::table('user_leave_balances', function (Blueprint $table) {
            $table->dropIndex('idx_user_leave_balances_user_year');
        });
    }
};
