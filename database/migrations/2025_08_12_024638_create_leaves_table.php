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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pengganti_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('kabag-pincab_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('total_hari')->default(0);
            $table->text('alasan');

            // Status tiap approval
            // $table->enum('status_pengganti', ['pending', 'approved', 'rejected'])->default('pending');
            // $table->enum('status_kabag-pincab', ['pending', 'approved', 'rejected'])->default('pending');
            // $table->enum('status_hrd', ['pending', 'approved', 'rejected'])->default('pending');

            // Status final cuti
            $table->enum('status_final', ['pending', 'approved', 'rejected', 'revision_requested', 'revision_accepted', 'revision_rejected'])->default('pending');
            // tambahan status izin mendadak
            $table->boolean('is_mendadak')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};
