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
        Schema::create('public_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('name');
            // Membedakan libur nasional (tidak potong kuota) vs cuti bersama (potong kuota)
            $table->enum('type', ['national_holiday', 'joint_leave'])->default('national_holiday');
            $table->year('year')->index();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            // Satu tanggal kalender = satu record. Kalau dua peristiwa bertepatan
            // di tanggal yang sama, gabungkan namanya jadi satu string, bukan dua baris.
            $table->unique('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_holidays');
    }
};
