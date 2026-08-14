<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuotaSetting;

class QuotaSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        QuotaSetting::setValue('auto_generate_leave_balances', true, 'boolean', 'Otomatis buat saldo cuti untuk user baru');
        QuotaSetting::setValue('default_annual_leave_quota', 12, 'integer', 'Kuota cuti tahunan default');
    }
}
