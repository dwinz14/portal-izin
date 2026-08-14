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
            ['nama_jabatan' => 'head cs', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'customer service', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'head teller', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'teller', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'kasie marketing kredit', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'kasie collection', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'account officer', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'admin kredit', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'accounting', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'tabungan deposito', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'sekretaris direksi', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'kabag operasional', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'kasie IT', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'staff IT', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'hrd', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'security', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'driver', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'ob', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'kabag APUPPT', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'staff APUPPT', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'kabag skai', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'staf skai', 'created_at' => now(), 'updated_at' => now()],
            ['nama_jabatan' => 'pincab', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
