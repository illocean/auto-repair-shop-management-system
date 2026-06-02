<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        try {
            DB::table('permissions')->insert([
                ['code' => 'customer.create', 'description' => 'Create customer records',           'module' => 'customer',     'created_at' => now()],
                ['code' => 'customer.read',   'description' => 'View customer records',             'module' => 'customer',     'created_at' => now()],
                ['code' => 'customer.update', 'description' => 'Edit customer records',             'module' => 'customer',     'created_at' => now()],
                ['code' => 'customer.delete', 'description' => 'Delete customer records',           'module' => 'customer',     'created_at' => now()],
                ['code' => 'vehicle.create',  'description' => 'Create vehicle records',            'module' => 'vehicle',      'created_at' => now()],
                ['code' => 'vehicle.read',    'description' => 'View vehicle records',              'module' => 'vehicle',      'created_at' => now()],
                ['code' => 'vehicle.update',  'description' => 'Edit vehicle records',              'module' => 'vehicle',      'created_at' => now()],
                ['code' => 'vehicle.delete',  'description' => 'Delete vehicle records',            'module' => 'vehicle',      'created_at' => now()],
                ['code' => 'repair_order.create', 'description' => 'Create repair orders',          'module' => 'repair_order', 'created_at' => now()],
                ['code' => 'repair_order.read',   'description' => 'View repair orders',            'module' => 'repair_order', 'created_at' => now()],
                ['code' => 'repair_order.update', 'description' => 'Edit repair orders',            'module' => 'repair_order', 'created_at' => now()],
                ['code' => 'repair_order.delete', 'description' => 'Delete repair orders',          'module' => 'repair_order', 'created_at' => now()],
                ['code' => 'service_type.create', 'description' => 'Create service type definitions','module' => 'service_type', 'created_at' => now()],
                ['code' => 'service_type.read',   'description' => 'View service type definitions',  'module' => 'service_type', 'created_at' => now()],
                ['code' => 'service_type.update', 'description' => 'Edit service type definitions',  'module' => 'service_type', 'created_at' => now()],
                ['code' => 'service_type.delete', 'description' => 'Delete service type definitions', 'module' => 'service_type', 'created_at' => now()],
                ['code' => 'roles.manage',    'description' => 'Assign roles and permissions',      'module' => 'admin',        'created_at' => now()],
                ['code' => 'audit.view',      'description' => 'View audit log',                    'module' => 'admin',        'created_at' => now()],
                ['code' => 'users.manage',    'description' => 'Create and manage users',           'module' => 'admin',        'created_at' => now()],
            ]);
            Log::info('Permissions seeded successfully');
        } catch (Throwable $e) {
            Log::error('PermissionSeeder failed: ' . $e->getMessage());
        }
    }
}
