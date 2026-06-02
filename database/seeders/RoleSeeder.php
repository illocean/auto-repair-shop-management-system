<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        try {
            DB::table('roles')->insert([
                ['name' => 'admin',    'display_name' => 'Administrator', 'created_at' => now()],
                ['name' => 'manager',  'display_name' => 'Manager',      'created_at' => now()],
                ['name' => 'staff',    'display_name' => 'Staff',        'created_at' => now()],
            ]);
            Log::info('Roles seeded successfully');
        } catch (Throwable $e) {
            Log::error('RoleSeeder failed: ' . $e->getMessage());
        }
    }
}
