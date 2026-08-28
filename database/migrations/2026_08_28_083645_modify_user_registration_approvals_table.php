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
        Schema::table('user_registration_approvals', function (Blueprint $table) {
            // Hapus FK lama, jadikan nullable, tambah FK baru dengan set null
            $table->dropForeign(['approved_by']);
            $table->unsignedBigInteger('approved_by')->nullable()->change();
            $table->foreign('approved_by')
                ->references('id')->on('users')
                ->onDelete('set null');

            // Tambah kolom baru
            $table->string('user_nik', 11)->nullable()->after('user_email');
            $table->enum('verified_via', ['otp', 'admin'])->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_registration_approvals', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->unsignedBigInteger('approved_by')->nullable(false)->change();
            $table->foreign('approved_by')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->dropColumn(['user_nik', 'verified_via']);
        });
    }
};
