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
        Schema::create('leave_pengganti_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_id')->constrained()->onDelete('cascade');
            $table->foreignId('old_pengganti_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('new_pengganti_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->text('alasan')->nullable();
            $table->timestamps();

            $table->index('leave_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_pengganti_changes');
    }
};
