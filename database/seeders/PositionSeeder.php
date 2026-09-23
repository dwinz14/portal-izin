<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('positions')->insert([
            ['nama_jabatan' => 'DIREKTUR UTAMA',                  'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'DIREKTUR BISNIS',                 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'DIREKTUR KEPATUHAN',              'created_at' => now(), 'updated_at' => now()],

            ['nama_jabatan' => 'KEPALA BAGIAN BISNIS',            'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KEPALA BAGIAN HRD',               'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KEPALA BAGIAN KEPATUHAN & MANRISK', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KEPALA BAGIAN OPERASIONAL',       'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KEPALA BAGIAN SKAI',              'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'PIMPINAN CABANG',                 'created_at' => now(), 'updated_at' => now()],

            ['nama_jabatan' => 'KASIE COLLECTION',                'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KASIE CUSTOMER SERVICE',          'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KASIE IT',                        'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KASIE MARKETING',                 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KASIE OPERASIONAL',               'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KEPALA KANTOR KAS',               'created_at' => now(), 'updated_at' => now()],

            ['nama_jabatan' => 'ACCOUNT OFFICER STAFF',            'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'ACCOUNTING STAFF',                 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'ADMIN KREDIT STAFF',               'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'HEAD TELLER',                      'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'CUSTOMER SERVICE',                 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'TELLER',                           'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KEPATUHAN & MANRISK STAFF',        'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'KOORDINATOR KREDIT',               'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'SEKRETARIS DIREKSI',               'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'STAFF IT',                         'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'STAFF SKAI',                       'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'TABUNGAN DEPOSITO',                'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'SECURITY',                'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'DRIVER',                'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'OB',                'created_at' => now(), 'updated_at' => now()],

        ]);
    }
}
