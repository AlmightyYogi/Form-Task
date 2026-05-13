<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExternalTeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mst_external_teams')->insert([
            [
                'id' => 1,
                'name' => 'L3',
                'description' => 'Layer 3 for Developer',
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Kyndryl Windows Team',
                'description' => 'Kyndryl Windows Team',
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'Kyndryl Linux Team',
                'description' => 'Kyndryl Linux Team',
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Kyndryl Network Team',
                'description' => 'Kyndryl Network Team',
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Kyndryl GCP Team',
                'description' => 'Kyndryl GCP Team',
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'id' => 6,
                'name' => 'IOH Security Team',
                'description' => 'IOH Security Team',
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'id' => 7,
                'name' => 'IOH Firewall Operation Team',
                'description' => 'IOH Firewall Operation Team',
                'is_active' => true,
                'created_at' => now(),
            ],
            [
                'id' => 8,
                'name' => 'IOH Defensive Security Team',
                'description' => 'IOH Defensive Security Team',
                'is_active' => true,
                'created_at' => now(),
            ],
        ]);
    }
};