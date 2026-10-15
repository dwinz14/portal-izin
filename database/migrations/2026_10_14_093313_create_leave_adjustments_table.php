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
        Schema::create('leave_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);                       // cancelled | rescheduled
            $table->unsignedBigInteger('leave_id');           // tanpa FK (lihat catatan di atas)
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();    // atasan pelaku
            $table->foreignId('employee_id')->nullable()->constrained('users')->nullOnDelete(); // pemilik cuti
            $table->text('alasan');
            $table->boolean('is_retroactive')->default(false); // dilakukan setelah periode cuti berakhir
            $table->json('before');                           // kondisi cuti sebelum perubahan
            $table->json('after')->nullable();                // kondisi sesudah (null untuk pembatalan)
            $table->json('quota')->nullable();                // perubahan saldo kuota (tahun, used/remaining)
            $table->json('snapshot')->nullable();             // pelaku, pemilik, rantai approval saat itu
            $table->timestamps();

            $table->index('leave_id');
            $table->index(['employee_id', 'created_at']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_adjustments');
    }
};
