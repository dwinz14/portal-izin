<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            DELETE ulb1
            FROM user_leave_balances ulb1
            INNER JOIN user_leave_balances ulb2
                ON  ulb1.user_id       = ulb2.user_id
                AND ulb1.leave_type_id = ulb2.leave_type_id
                AND ulb1.year          = ulb2.year
                AND ulb1.id            < ulb2.id
        ");

        Schema::table('user_leave_balances', function (Blueprint $table) {
            $table->dropIndex(
                'user_leave_balances_user_id_leave_type_id_year_index'
            );

            $table->unique(['user_id', 'leave_type_id', 'year'], 'ulb_user_type_year_unique');
        });
    }

    public function down(): void
    {
        Schema::table('user_leave_balances', function (Blueprint $table) {
            $table->dropUnique('ulb_user_type_year_unique');
        });
    }
};
