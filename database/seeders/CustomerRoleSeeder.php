<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerRoleSeeder extends Seeder
{
    public function run(): void
    {
        $exists = DB::table('roles')->where('name', 'customer')->exists();

        if (!$exists) {
            DB::table('roles')->insert([
                'name'         => 'customer',
                'display_name' => 'Customer',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);
        }
    }
}
