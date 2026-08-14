<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\LeaveType::create([
            'name' => 'cuti tahunan',
            'quota' => 12,
            'gender' => null,
            'min_years' => 1,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'cuti melahirkan',
            'quota' => 90,
            'gender' => 'P', // hanya untuk perempuan
            'min_years' => 0,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'izin sakit dengan surat dokter',
            'quota' => 0, // tanpa batas
            'gender' => null,
            'min_years' => 0,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'izin sakit tanpa surat dokter',
            'quota' => 0, // tanpa batas
            'gender' => null,
            'min_years' => 0,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'izin pernikahan anak karyawan',
            'quota' => 2,
            'gender' => null,
            'min_years' => 0,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'izin melangsungkan pernikahan',
            'quota' => 2,
            'gender' => null,
            'min_years' => 0,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'izin mengkhitankan/membaptis anak',
            'quota' => 2,
            'gender' => null,
            'min_years' => 0,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'izin persalinan istri',
            'quota' => 2,
            'gender' => null,
            'min_years' => 0,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'izin kematian orang tua/mertua/suami/istri/anak',
            'quota' => 2,
            'gender' => null,
            'min_years' => 0,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'izin kematian keluarga dalam satu rumah',
            'quota' => 1,
            'gender' => null,
            'min_years' => 0,
            'is_active' => true,
        ]);
        \App\Models\LeaveType::create([
            'name' => 'izin pernikahan saudara kandung',
            'quota' => 1,
            'gender' => null,
            'min_years' => 0,
            'is_active' => true,
        ]);
    }
}
