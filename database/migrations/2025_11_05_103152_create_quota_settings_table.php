<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quota_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('quota_settings')->insert([
            [
                'key' => 'auto_generate_leave_balances',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Otomatis buat saldo cuti untuk user baru',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'default_annual_leave_quota',
                'value' => '12',
                'type' => 'integer',
                'description' => 'Kuota cuti tahunan default',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quota_settings');
    }
};
