<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // Composite index untuk query per user + filter tanggal (dipakai di halaman show)
            $table->index(
                ['causer_id', 'causer_type', 'created_at'],
                'al_causer_date_idx'
            );

            // Index untuk feed global: log_name + tanggal (dipakai di halaman index)
            $table->index(
                ['log_name', 'created_at'],
                'al_logname_date_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndex('al_causer_date_idx');
            $table->dropIndex('al_logname_date_idx');
        });
    }
};
