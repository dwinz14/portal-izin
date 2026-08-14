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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('step'); // urutan approval
            $table->enum('status', ['pending', 'approved', 'rejected', 'revision_requested', 'revision_accepted', 'revision_rejected'])->default('pending');
            $table->timestamps();
            $table->unique(['leave_id', 'step'], 'approvals_leave_id_step_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};
