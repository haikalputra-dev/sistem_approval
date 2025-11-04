<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Admin',
            'Pemohon',
            'Direksi',
            'PYB1',
            'PYB2',
            'BO',
        ];

        $data = [];
        $now = Carbon::now();

        foreach ($roles as $role) {
            $data[] = [
                'role_name' => $role,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('roles')->insert($data);
    }
}
