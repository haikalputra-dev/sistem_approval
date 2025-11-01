<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PerusahaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        DB::table('perusahaans')->insert([
            [
                'nama_perusahaan' => 'PT. Induk Sejahtera',
                'alamat' => 'Jl. Jend. Sudirman No. 1, Jakarta',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_perusahaan' => 'PT. Anak Perusahaan Maju',
                'alamat' => 'Jl. M.H. Thamrin No. 10, Jakarta',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'nama_perusahaan' => 'PT. Sinergi Bisnis Utama',
                'alamat' => 'Jl. Gatot Subroto No. 5, Jakarta',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
