<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'id' => 1,
                'name' => 'admin',
                'created_at' => '2026-04-10 04:07:34',
                'updated_at' => '2026-04-10 04:07:34',
            ],
            [
                'id' => 2,
                'name' => 'viewer',
                'created_at' => '2026-04-10 04:07:34',
                'updated_at' => '2026-04-10 04:07:34',
            ],
            [
                'id' => 3,
                'name' => 'user',
                'created_at' => '2026-04-10 04:07:34',
                'updated_at' => '2026-04-10 04:07:34',
            ],
        ]);
    }
}