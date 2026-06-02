<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Customers
            $c1 = DB::table('customers')->insertGetId([
                'first_name' => 'Juan', 'last_name' => 'Dela Cruz',
                'email' => 'juan@email.com', 'phone' => '09171234567',
                'address' => '123 Rizal St, Manila', 'created_at' => now(),
            ]);
            $c2 = DB::table('customers')->insertGetId([
                'first_name' => 'Maria', 'last_name' => 'Santos',
                'email' => 'maria@email.com', 'phone' => '09179876543',
                'address' => '456 Mabini Ave, Quezon City', 'created_at' => now(),
            ]);
            $c3 = DB::table('customers')->insertGetId([
                'first_name' => 'Pedro', 'last_name' => 'Gonzales',
                'email' => 'pedro@email.com', 'phone' => '09221112233',
                'address' => '789 Aguinaldo Hwy, Makati', 'created_at' => now(),
            ]);

            // Vehicles
            DB::table('vehicles')->insert([
                ['customer_id' => $c1, 'make' => 'Toyota',  'model' => 'Vios',    'year' => 2020, 'license_plate' => 'ABC1234', 'vin' => 'JTDBT123456789012', 'created_at' => now()],
                ['customer_id' => $c1, 'make' => 'Honda',   'model' => 'Civic',   'year' => 2022, 'license_plate' => 'DEF5678', 'vin' => 'SHHFK123456789013', 'created_at' => now()],
                ['customer_id' => $c2, 'make' => 'Mitsubishi', 'model' => 'Montero', 'year' => 2021, 'license_plate' => 'GHI9012', 'vin' => 'MMBJN123456789014', 'created_at' => now()],
                ['customer_id' => $c3, 'make' => 'Ford',    'model' => 'Ranger',  'year' => 2023, 'license_plate' => 'JKL3456', 'vin' => '1FTER123456789015', 'created_at' => now()],
            ]);

            Log::info('Sample data seeded: 3 customers, 4 vehicles');
        } catch (Throwable $e) {
            Log::error('SampleDataSeeder failed: ' . $e->getMessage());
        }
    }
}
