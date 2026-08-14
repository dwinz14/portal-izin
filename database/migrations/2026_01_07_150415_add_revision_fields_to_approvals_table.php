<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->date('revised_start_date')->nullable()->after('status');
            $table->date('revised_end_date')->nullable()->after('revised_start_date');
            $table->integer('revised_total_hari')->nullable()->after('revised_end_date');
            $table->timestamp('revised_at')->nullable()->after('revised_total_hari');
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->boolean('is_revision_pending')->default(false)->after('status_final');
            $table->unsignedBigInteger('revision_by_approval_id')->nullable()->after('is_revision_pending');
        });
    }

    public function down()
    {
        Schema::table('approvals', function (Blueprint $table) {
            $table->dropColumn(['revised_start_date', 'revised_end_date', 'revised_total_hari', 'revised_at']);
        });

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn(['is_revision_pending', 'revision_by_approval_id']);
        });
    }
};
