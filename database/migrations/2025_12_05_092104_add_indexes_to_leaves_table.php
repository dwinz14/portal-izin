<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status_final');
            $table->index(['user_id', 'status_final']);
            $table->index('start_date');
            $table->index('end_date');
            $table->index('leave_type_id');
        });

        Schema::table('approvals', function (Blueprint $table) {
            $table->index('approver_id');
            $table->index('status');
            $table->index('step');
            $table->index(['leave_id', 'step']);
        });

        Schema::table('user_leave_balances', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('leave_type_id');
            $table->index('year');
            $table->index(['user_id', 'leave_type_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leaves', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status_final']);
            $table->dropIndex(['user_id', 'status_final']);
            $table->dropIndex(['start_date']);
            $table->dropIndex(['end_date']);
            $table->dropIndex(['leave_type_id']);
        });
    }
};
