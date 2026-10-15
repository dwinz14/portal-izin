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
        if (Schema::hasColumn('leaves', 'quota_year')) {
            return;
        }

        Schema::table('leaves', function (Blueprint $table) {
            $table->unsignedSmallInteger('quota_year')->nullable()->after('total_hari');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('leaves', 'quota_year')) {
            return;
        }

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn('quota_year');
        });
    }
};
