<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'id' => '019d8aa2-0b22-72d9-b96c-c6b31dce4ac6',
                'role_id' => 1,
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$/z6cSsi2Z5jq./jH3hsZdOZPtx/OwD6/Q583.9/gOXav5rxIWh2QS',
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => null,
                'access_failed_count' => 2,
                'lockout_enabled' => true,
                'lockout_end' => null,
                'created_at' => '2026-04-14 06:16:08',
                'updated_at' => '2026-05-06 07:26:40',
            ],
            [
                'id' => '019db49b-dc33-704f-bde1-311862eed609',
                'role_id' => 3,
                'name' => 'Aldian Prawira',
                'email' => 'aldianprawira99@gmail.com',
                'email_verified_at' => null,
                'password' => '$2y$12$1iH2UEMC7KTsd2DTu1nkw.NgqvI6xsDJdmri4kq.LMkqxeR4aFLnm',
                'two_factor_secret' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_confirmed_at' => null,
                'remember_token' => null,
                'access_failed_count' => 0,
                'lockout_enabled' => true,
                'lockout_end' => null,
                'created_at' => '2026-04-22 09:53:26',
                'updated_at' => '2026-04-22 09:53:26',
            ],
        ]);
    }
}