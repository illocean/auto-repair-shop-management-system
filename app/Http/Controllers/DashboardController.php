<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class DashboardController extends Controller
{
    public function index()
    {
        // Customer dashboard — show their vehicles and orders only
        if (session('role') === 'customer') {
            return $this->customerDashboard();
        }

        // Staff dashboard — full overview
        return $this->staffDashboard();
    }

    private function customerDashboard()
    {
        $vehicles = collect(); $orders = collect();
        try {
            $customer = DB::table('customers')->where('user_id', session('user_id'))->first();

            if ($customer) {
                $vehicles = DB::table('vehicles')
                    ->where('customer_id', $customer->id)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $orders = DB::table('repair_orders')
                    ->join('vehicles', 'repair_orders.vehicle_id', 'vehicles.id')
                    ->select('repair_orders.*', 'vehicles.make', 'vehicles.model', 'vehicles.license_plate')
                    ->where('repair_orders.customer_id', $customer->id)
                    ->orderBy('repair_orders.created_at', 'desc')
                    ->get();
            }

            Log::info("Customer dashboard: " . ($customer ? $customer->email : 'no customer link'));
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        }

        return view('Dashboard.customer', compact('vehicles', 'orders'));
    }

    private function staffDashboard()
    {
        $customerCount = 0; $vehicleCount = 0; $openOrders = 0; $completedOrders = 0;
        $appointmentCount = 0; $supplyCount = 0; $lowStockCount = 0; $totalRevenue = 0;
        $recentOrders = collect(); $upcomingAppointments = collect();
        try {
            Log::debug("=================== staff dashboard ===================");
            $customerCount = DB::table('customers')->count();
            $vehicleCount = DB::table('vehicles')->count();
            $openOrders = DB::table('repair_orders')->whereIn('status', ['open', 'in_progress'])->count();
            $completedOrders = DB::table('repair_orders')->where('status', 'completed')->count();
            $appointmentCount = DB::table('appointments')->whereNotIn('status', ['cancelled'])->count();
            $supplyCount = DB::table('supplies')->count();
            $lowStockCount = DB::table('supplies')->whereColumn('quantity', '<=', 'low_stock_threshold')->count();
            $totalRevenue = DB::table('repair_order_services')
                ->join('repair_orders', 'repair_order_services.repair_order_id', 'repair_orders.id')
                ->where('repair_orders.status', 'completed')
                ->sum('repair_order_services.line_total');

            $recentOrders = DB::table('repair_orders')
                ->join('customers', 'repair_orders.customer_id', 'customers.id')
                ->join('vehicles', 'repair_orders.vehicle_id', 'vehicles.id')
                ->select(
                    'repair_orders.*',
                    'customers.first_name as cust_first',
                    'customers.last_name as cust_last',
                    'vehicles.make',
                    'vehicles.model',
                    'vehicles.year',
                    'vehicles.license_plate'
                )
                ->orderBy('repair_orders.created_at', 'desc')
                ->limit(5)
                ->get();

            $upcomingAppointments = DB::table('appointments')
                ->join('customers', 'appointments.customer_id', 'customers.id')
                ->join('vehicles', 'appointments.vehicle_id', 'vehicles.id')
                ->select(
                    'appointments.*',
                    'customers.first_name as cust_first',
                    'customers.last_name as cust_last',
                    'vehicles.make',
                    'vehicles.model',
                    'vehicles.license_plate',
                )
                ->whereIn('appointments.status', ['scheduled', 'confirmed'])
                ->where('appointment_date', '>=', now()->format('Y-m-d'))
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->limit(5)
                ->get();

            Log::info("Staff dashboard: {$customerCount} customers, {$openOrders} open orders, {$appointmentCount} appointments");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end staff dashboard ===================");
        }
        return view('Dashboard.index', compact(
            'customerCount', 'vehicleCount', 'openOrders', 'completedOrders',
            'appointmentCount', 'supplyCount', 'lowStockCount', 'totalRevenue',
            'recentOrders', 'upcomingAppointments'
        ));
    }
}
