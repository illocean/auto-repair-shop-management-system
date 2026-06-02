<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Throwable;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $adminRoleId = DB::table('roles')->where('name', 'admin')->value('id');
            $managerRoleId = DB::table('roles')->where('name', 'manager')->value('id');
            $staffRoleId = DB::table('roles')->where('name', 'staff')->value('id');

            $users = [
                [
                    'first_name' => 'System',
                    'last_name'  => 'Administrator',
                    'email'      => 'admin@system.local',
                    'password'   => 'admin123',
                    'role_id'    => $adminRoleId,
                ],
                [
                    'first_name' => 'Juan',
                    'last_name'  => 'Dela Cruz',
                    'email'      => 'juan@repairshop.local',
                    'password'   => 'password',
                    'role_id'    => $managerRoleId,
                ],
                [
                    'first_name' => 'Maria',
                    'last_name'  => 'Santos',
                    'email'      => 'maria@repairshop.local',
                    'password'   => 'password',
                    'role_id'    => $managerRoleId,
                ],
                [
                    'first_name' => 'Pedro',
                    'last_name'  => 'Reyes',
                    'email'      => 'pedro@repairshop.local',
                    'password'   => 'password',
                    'role_id'    => $staffRoleId,
                ],
                [
                    'first_name' => 'Ana',
                    'last_name'  => 'Gonzales',
                    'email'      => 'ana@repairshop.local',
                    'password'   => 'password',
                    'role_id'    => $staffRoleId,
                ],
            ];

            foreach ($users as $data) {
                $userId = DB::table('users')->insertGetId([
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'email'      => $data['email'],
                    'password'   => Hash::make($data['password']),
                    'is_active'  => true,
                    'user_type'  => 'staff',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('role_user')->insert([
                    'user_id' => $userId,
                    'role_id' => $data['role_id'],
                ]);

                Log::info("User seeded: {$data['email']} / {$data['password']} ({$data['role_id']})");
            }
        } catch (Throwable $e) {
            Log::error('UserSeeder failed: ' . $e->getMessage());
        }
    }
}
