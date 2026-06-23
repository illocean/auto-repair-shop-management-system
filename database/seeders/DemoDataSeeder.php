<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $this->seedRepairOrders();
            $this->seedAppointments();
            $this->seedSupplies();

            Log::info('Demo data seeded: repair orders, appointments, supplies');
        } catch (Throwable $e) {
            Log::error('DemoDataSeeder failed: ' . $e->getMessage());
        }
    }

    private function seedRepairOrders(): void
    {
        // Customer 1 -> vehicles 1 (Vios), 2 (Civic)
        // Customer 2 -> vehicle 3 (Montero)
        // Customer 3 -> vehicle 4 (Ranger)

        $orders = [
            // Completed orders (older dates)
            [
                'customer_id' => 1, 'vehicle_id' => 1,
                'service_advisor_name' => 'Pedro Reyes',
                'order_date' => now()->subDays(28)->format('Y-m-d'),
                'status' => 'completed',
                'notes' => 'Regular maintenance — oil change and tire rotation.',
                'services' => [1, 3], // Oil Change + Rotate Tires
            ],
            [
                'customer_id' => 2, 'vehicle_id' => 3,
                'service_advisor_name' => 'Ana Gonzales',
                'order_date' => now()->subDays(21)->format('Y-m-d'),
                'status' => 'completed',
                'notes' => 'Major service — transmission fluid replacement and brake inspection.',
                'services' => [8, 4], // Transmission Service + Brake Inspection
            ],
            [
                'customer_id' => 3, 'vehicle_id' => 4,
                'service_advisor_name' => 'Pedro Reyes',
                'order_date' => now()->subDays(14)->format('Y-m-d'),
                'status' => 'completed',
                'notes' => 'Coolant flush and battery test.',
                'services' => [7, 6], // Coolant Flush + Battery Test
            ],
            [
                'customer_id' => 1, 'vehicle_id' => 2,
                'service_advisor_name' => 'Juan Dela Cruz',
                'order_date' => now()->subDays(10)->format('Y-m-d'),
                'status' => 'completed',
                'notes' => 'Air filter replacement and general inspection.',
                'services' => [5, 4], // Air Filter + Brake Inspection
            ],
            // In-progress orders
            [
                'customer_id' => 1, 'vehicle_id' => 1,
                'service_advisor_name' => 'Ana Gonzales',
                'order_date' => now()->subDays(3)->format('Y-m-d'),
                'status' => 'in_progress',
                'notes' => 'Customer reported engine knocking. Diagnosis in progress.',
                'services' => [4, 7], // Brake Inspection + Coolant Flush
            ],
            [
                'customer_id' => 2, 'vehicle_id' => 3,
                'service_advisor_name' => 'Pedro Reyes',
                'order_date' => now()->subDays(2)->format('Y-m-d'),
                'status' => 'in_progress',
                'notes' => 'Routine servicing — oil change and lubrication.',
                'services' => [1, 2], // Oil Change + Lubrication
            ],
            // Open orders
            [
                'customer_id' => 3, 'vehicle_id' => 4,
                'service_advisor_name' => 'Juan Dela Cruz',
                'order_date' => now()->subDay()->format('Y-m-d'),
                'status' => 'open',
                'notes' => 'Check engine light on. Full diagnostic needed.',
                'services' => [4, 5, 6], // Brake Inspection + Air Filter + Battery
            ],
            [
                'customer_id' => 1, 'vehicle_id' => 2,
                'service_advisor_name' => 'Ana Gonzales',
                'order_date' => now()->format('Y-m-d'),
                'status' => 'open',
                'notes' => 'New customer walk-in — tire rotation and oil change.',
                'services' => [3, 1], // Rotate Tires + Oil Change
            ],
            // Cancelled orders
            [
                'customer_id' => 2, 'vehicle_id' => 3,
                'service_advisor_name' => 'Pedro Reyes',
                'order_date' => now()->subDays(7)->format('Y-m-d'),
                'status' => 'cancelled',
                'notes' => 'Customer decided to take vehicle to dealership instead.',
                'services' => [8], // Transmission Service
            ],
            [
                'customer_id' => 3, 'vehicle_id' => 4,
                'service_advisor_name' => 'Ana Gonzales',
                'order_date' => now()->subDays(5)->format('Y-m-d'),
                'status' => 'cancelled',
                'notes' => 'Parts not available — customer rescheduled.',
                'services' => [7, 5], // Coolant Flush + Air Filter
            ],
        ];

        // Service type pricing lookup
        $servicePricing = [
            1 => ['book_hours' => 0.50, 'rate_per_hour' => 95.00],  // Oil Change
            2 => ['book_hours' => 0.30, 'rate_per_hour' => 85.00],  // Lubrication
            3 => ['book_hours' => 0.50, 'rate_per_hour' => 90.00],  // Rotate Tires
            4 => ['book_hours' => 1.00, 'rate_per_hour' => 100.00], // Brake Inspection
            5 => ['book_hours' => 0.30, 'rate_per_hour' => 80.00],  // Air Filter
            6 => ['book_hours' => 0.50, 'rate_per_hour' => 95.00],  // Battery
            7 => ['book_hours' => 1.00, 'rate_per_hour' => 90.00],  // Coolant Flush
            8 => ['book_hours' => 1.50, 'rate_per_hour' => 110.00], // Transmission Service
        ];

        foreach ($orders as $order) {
            $orderId = DB::table('repair_orders')->insertGetId([
                'customer_id'         => $order['customer_id'],
                'vehicle_id'          => $order['vehicle_id'],
                'service_advisor_name'=> $order['service_advisor_name'],
                'order_date'          => $order['order_date'],
                'status'              => $order['status'],
                'notes'               => $order['notes'],
                'created_by'          => null,
                'updated_by'          => null,
                'created_at'          => $order['order_date'] . ' 08:00:00',
                'updated_at'          => $order['order_date'] . ' 08:00:00',
            ]);

            foreach ($order['services'] as $serviceTypeId) {
                $pricing = $servicePricing[$serviceTypeId];
                $lineTotal = round($pricing['book_hours'] * $pricing['rate_per_hour'], 2);

                DB::table('repair_order_services')->insert([
                    'repair_order_id' => $orderId,
                    'service_type_id' => $serviceTypeId,
                    'book_hours'      => $pricing['book_hours'],
                    'rate_per_hour'   => $pricing['rate_per_hour'],
                    'line_total'      => $lineTotal,
                    'created_at'      => $order['order_date'] . ' 08:00:00',
                    'updated_at'      => $order['order_date'] . ' 08:00:00',
                ]);
            }
        }
    }

    private function seedAppointments(): void
    {
        // Past appointments
        DB::table('appointments')->insert([
            'customer_id'      => 1,
            'vehicle_id'       => 1,
            'appointment_date' => now()->subDays(15)->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'status'           => 'completed',
            'notes'            => 'Routine oil change',
            'created_by'       => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('appointments')->insert([
            'customer_id'      => 3,
            'vehicle_id'       => 4,
            'appointment_date' => now()->subDays(8)->format('Y-m-d'),
            'appointment_time' => '14:30:00',
            'status'           => 'cancelled',
            'notes'            => 'Cancelled — customer called to reschedule',
            'created_by'       => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        // Upcoming appointments
        DB::table('appointments')->insert([
            'customer_id'      => 2,
            'vehicle_id'       => 3,
            'appointment_date' => now()->addDays(2)->format('Y-m-d'),
            'appointment_time' => '08:30:00',
            'status'           => 'confirmed',
            'notes'            => 'Transmission service — customer called ahead',
            'created_by'       => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('appointments')->insert([
            'customer_id'      => 1,
            'vehicle_id'       => 2,
            'appointment_date' => now()->addDays(4)->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'status'           => 'scheduled',
            'notes'            => 'Air conditioning check and recharge',
            'created_by'       => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('appointments')->insert([
            'customer_id'      => 3,
            'vehicle_id'       => 4,
            'appointment_date' => now()->addDays(7)->format('Y-m-d'),
            'appointment_time' => '11:00:00',
            'status'           => 'scheduled',
            'notes'            => 'Brake pad replacement quote',
            'created_by'       => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        DB::table('appointments')->insert([
            'customer_id'      => 1,
            'vehicle_id'       => 1,
            'appointment_date' => now()->addDays(10)->format('Y-m-d'),
            'appointment_time' => '15:00:00',
            'status'           => 'scheduled',
            'notes'            => 'Follow-up after engine diagnostic',
            'created_by'       => null,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    private function seedSupplies(): void
    {
        $supplies = [
            [
                'name'                => 'Engine Oil 5W-30 (1 qt)',
                'description'         => 'Synthetic blend engine oil, SAE 5W-30',
                'quantity'            => 24,
                'unit'                => 'quart',
                'unit_price'          => 6.50,
                'low_stock_threshold' => 10,
            ],
            [
                'name'                => 'Engine Oil 10W-40 (1 qt)',
                'description'         => 'High-mileage engine oil, SAE 10W-40',
                'quantity'            => 12,
                'unit'                => 'quart',
                'unit_price'          => 7.25,
                'low_stock_threshold' => 10,
            ],
            [
                'name'                => 'Oil Filter',
                'description'         => 'Standard spin-on oil filter, fits most Toyota/Honda',
                'quantity'            => 3,
                'unit'                => 'piece',
                'unit_price'          => 8.50,
                'low_stock_threshold' => 10,  // LOW STOCK: 3 < 10
            ],
            [
                'name'                => 'Engine Air Filter',
                'description'         => 'Panel air filter, universal fit',
                'quantity'            => 8,
                'unit'                => 'piece',
                'unit_price'          => 12.00,
                'low_stock_threshold' => 5,
            ],
            [
                'name'                => 'Cabin Air Filter',
                'description'         => 'Activated carbon cabin air filter',
                'quantity'            => 2,
                'unit'                => 'piece',
                'unit_price'          => 15.00,
                'low_stock_threshold' => 5,  // LOW STOCK: 2 < 5
            ],
            [
                'name'                => 'Brake Pads (Front)',
                'description'         => 'Ceramic disc brake pads, front set',
                'quantity'            => 6,
                'unit'                => 'set',
                'unit_price'          => 45.00,
                'low_stock_threshold' => 4,
            ],
            [
                'name'                => 'Brake Pads (Rear)',
                'description'         => 'Ceramic disc brake pads, rear set',
                'quantity'            => 4,
                'unit'                => 'set',
                'unit_price'          => 42.00,
                'low_stock_threshold' => 4,
            ],
            [
                'name'                => 'Coolant / Antifreeze (1 gal)',
                'description'         => 'Universal 50/50 premix coolant, green',
                'quantity'            => 5,
                'unit'                => 'gallon',
                'unit_price'          => 18.00,
                'low_stock_threshold' => 3,
            ],
            [
                'name'                => 'Transmission Fluid (1 qt)',
                'description'         => 'ATF Dexron VI automatic transmission fluid',
                'quantity'            => 18,
                'unit'                => 'quart',
                'unit_price'          => 9.75,
                'low_stock_threshold' => 6,
            ],
            [
                'name'                => 'Wiper Blades (Pair)',
                'description'         => 'Beam-style windshield wiper blades, 24" + 20"',
                'quantity'            => 1,
                'unit'                => 'set',
                'unit_price'          => 22.00,
                'low_stock_threshold' => 4,  // LOW STOCK: 1 < 4
            ],
            [
                'name'                => 'Spark Plugs (Set of 4)',
                'description'         => 'Iridium-tipped spark plugs, standard size',
                'quantity'            => 3,
                'unit'                => 'set',
                'unit_price'          => 28.50,
                'low_stock_threshold' => 3,
            ],
        ];

        foreach ($supplies as $supply) {
            DB::table('supplies')->insert([
                'name'                => $supply['name'],
                'description'         => $supply['description'],
                'quantity'            => $supply['quantity'],
                'unit'                => $supply['unit'],
                'unit_price'          => $supply['unit_price'],
                'low_stock_threshold' => $supply['low_stock_threshold'],
                'created_at'          => now(),
                'updated_at'          => now(),
            ]);
        }
    }
}
