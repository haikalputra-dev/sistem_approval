<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID dari roles dan perusahaan
        $roleAdminId = DB::table('roles')->where('role_name', 'Admin')->value('id');
        $rolePemohonId = DB::table('roles')->where('role_name', 'Pemohon')->value('id');
        $roleDireksiId = DB::table('roles')->where('role_name', 'Direksi')->value('id');

        $perusahaan1Id = DB::table('perusahaans')->where('nama_perusahaan', 'PT. Induk Sejahtera')->value('id');
        $perusahaan2Id = DB::table('perusahaans')->where('nama_perusahaan', 'PT. Anak Perusahaan Maju')->value('id');

        $now = Carbon::now();

        DB::table('users')->insert([
            // 1. Admin
            [
                'name' => 'Admin Sistem',
                'email' => 'admin@sistemapproval.com',
                'password' => Hash::make('password'),
                'role_id' => $roleAdminId,
                'perusahaan_id' => $perusahaan1Id, // Admin di induk
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // 2. Pemohon
            [
                'name' => 'User Pemohon 1',
                'email' => 'pemohon1@sistemapproval.com',
                'password' => Hash::make('password'),
                'role_id' => $rolePemohonId,
                'perusahaan_id' => $perusahaan2Id, // Pemohon di anak perusahaan
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // 3. Direksi
            [
                'name' => 'Bapak Direksi',
                'email' => 'direksi@sistemapproval.com',
                'password' => Hash::make('password'),
                'role_id' => $roleDireksiId,
                'perusahaan_id' => $perusahaan1Id, // Direksi di induk
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
