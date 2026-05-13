<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AllowedEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('allowed_emails')->insert([
            [
                'id' => 1,
                'email' => '@gmail.com',
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'email' => '@lintasarta.co.id',
                'created_at' => now(),
            ],
        ]);
    }
};