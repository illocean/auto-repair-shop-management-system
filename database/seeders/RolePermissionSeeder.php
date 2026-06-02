<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
            $managerRoleId = DB::table('roles')->where('name', 'manager')->value('id');
            $staffRoleId = DB::table('roles')->where('name', 'staff')->value('id');

            $allPermissions = DB::table('permissions')->pluck('id');

            // Admin gets ALL permissions
            foreach ($allPermissions as $permId) {
                DB::table('permission_role')->insert([
                    'role_id' => $adminRoleId, 'permission_id' => $permId
                ]);
            }

            // Manager: customer/vehicle/repair CRUD (no delete), service read, audit view
            $managerCodes = [
                'customer.create', 'customer.read', 'customer.update',
                'vehicle.create', 'vehicle.read', 'vehicle.update',
                'repair_order.create', 'repair_order.read', 'repair_order.update',
                'service_type.read', 'audit.view',
            ];
            $managerPerms = DB::table('permissions')->whereIn('code', $managerCodes)->pluck('id');
            foreach ($managerPerms as $permId) {
                DB::table('permission_role')->insert([
                    'role_id' => $managerRoleId, 'permission_id' => $permId
                ]);
            }

            // Staff: basic read/create on repair, customer read, vehicle read
            $staffCodes = [
                'customer.read', 'vehicle.read',
                'repair_order.create', 'repair_order.read', 'repair_order.update',
            ];
            $staffPerms = DB::table('permissions')->whereIn('code', $staffCodes)->pluck('id');
            foreach ($staffPerms as $permId) {
                DB::table('permission_role')->insert([
                    'role_id' => $staffRoleId, 'permission_id' => $permId
                ]);
            }

            Log::info('Role permissions seeded successfully');
        } catch (Throwable $e) {
            Log::error('RolePermissionSeeder failed: ' . $e->getMessage());
        }
    }
}
