<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'nik' => 'AP111111111',
                'name' => 'super admin',
                'email' => 'admin@cutiapp.com',
                'password' => Hash::make('Superuser123!'),
                'role' => 'super_admin',
                'gender' => null,
                'status' => 'approved',
                'division_id' => null,
                'position_id' => null,
                'office_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
