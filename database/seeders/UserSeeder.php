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
        $rolePYB1Id = DB::table('roles')->where('role_name', 'PYB1')->value('id');
        $rolePYB2Id = DB::table('roles')->where('role_name', 'PYB2')->value('id');
        $roleBOId = DB::table('roles')->where('role_name', 'BO')->value('id');

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
                'perusahaan_id' => null,
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
                'perusahaan_id' => $perusahaan1Id,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // 3. PYB1
            [
                'name' => 'Pihak Yang Berwenang 1',
                'email' => 'pyb1@sistemapproval.com',
                'password' => Hash::make('password'),
                'role_id' => $rolePYB1Id,
                'perusahaan_id' => null ,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // 4. PYB2
            [
                'name' => 'Pihak Yang Berwenang 2',
                'email' => 'pyb2@sistemapproval.com',
                'password' => Hash::make('password'),
                'role_id' => $rolePYB2Id,
                'perusahaan_id' => null,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // 5. BO
            [
                'name' => 'Benneficial Owner',
                'email' => 'bo@sistemapproval.com',
                'password' => Hash::make('password'),
                'role_id' => $roleBOId,
                'perusahaan_id' => null,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
