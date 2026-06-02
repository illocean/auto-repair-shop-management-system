<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ServiceTypeSeeder extends Seeder
{
    public function run(): void
    {
        try {
            DB::table('service_types')->insert([
                [
                    'name'          => 'Oil Change',
                    'description'   => 'Replace engine oil and filter',
                    'book_hours'    => 0.50,
                    'rate_per_hour' => 95.00,
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'Lubrication',
                    'description'   => 'Chassis lubrication and grease fittings',
                    'book_hours'    => 0.30,
                    'rate_per_hour' => 85.00,
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'Rotate Tires',
                    'description'   => 'Rotate and balance all four tires',
                    'book_hours'    => 0.50,
                    'rate_per_hour' => 90.00,
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'Brake Inspection',
                    'description'   => 'Inspect brake pads, rotors, and fluid',
                    'book_hours'    => 1.00,
                    'rate_per_hour' => 100.00,
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'Air Filter Replacement',
                    'description'   => 'Replace engine air filter and cabin filter',
                    'book_hours'    => 0.30,
                    'rate_per_hour' => 80.00,
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'Battery Test & Replacement',
                    'description'   => 'Test battery health and replace if needed',
                    'book_hours'    => 0.50,
                    'rate_per_hour' => 95.00,
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'Coolant Flush',
                    'description'   => 'Flush and replace engine coolant',
                    'book_hours'    => 1.00,
                    'rate_per_hour' => 90.00,
                    'created_at'    => now(),
                ],
                [
                    'name'          => 'Transmission Service',
                    'description'   => 'Drain and fill transmission fluid',
                    'book_hours'    => 1.50,
                    'rate_per_hour' => 110.00,
                    'created_at'    => now(),
                ],
            ]);
            Log::info('Service types seeded successfully');
        } catch (Throwable $e) {
            Log::error('ServiceTypeSeeder failed: ' . $e->getMessage());
        }
    }
}
