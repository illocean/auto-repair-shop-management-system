<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\RepairOrder;
use App\Models\RepairOrderService;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class RepairOrderController extends Controller
{
    public function index()
    {
        $orders = collect();
        try {
            Log::debug("=================== repair order list ===================");
            $query = DB::table('repair_orders')
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
                );

            if (session('role') === 'customer') {
                $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
                if ($customer) {
                    $query->where('repair_orders.customer_id', $customer->id);
                }
            }

            $orders = $query->orderBy('repair_orders.order_date', 'desc')->get();
            Log::info("Repair orders: " . count($orders) . " records");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end repair order list ===================");
        }
        return view('RepairOrder.index', compact('orders'));
    }

    public function create()
    {
        $serviceTypes = collect(); $customer = null; $vehicles = collect(); $customers = collect();
        try {
            Log::debug("=================== repair order create form ===================");
            $serviceTypes = DB::table('service_types')->orderBy('name')->get();

            if (session('role') === 'customer') {
                $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
                if ($customer) {
                    $vehicles = DB::table('vehicles')
                        ->where('customer_id', $customer->id)
                        ->orderBy('make')
                        ->get();
                }
            } else {
                $customers = DB::table('customers')->orderBy('last_name')->get();
            }
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end repair order create form ===================");
        }
        return view('RepairOrder.create', compact('customer', 'customers', 'vehicles', 'serviceTypes'));
    }

    public function store(Request $request)
    {
        try {
            Log::debug("=================== repair order create ===================");

            if (session('role') === 'customer') {
                $request->validate([
                    'vehicle_id'           => ['required', 'exists:vehicles,id'],
                    'order_date'           => ['required', 'date'],
                    'notes'                => ['nullable', 'string'],
                    'service_ids'          => ['required', 'array', 'min:1'],
                    'service_ids.*'        => ['exists:service_types,id'],
                ]);

                $customerRecord = DB::table('customers')->where('user_id', session('user_id'))->first();
                if (!$customerRecord) {
                    return back()->withInput()->withErrors(['error' => 'No customer profile found. Contact support.']);
                }

                // Verify vehicle belongs to this customer
                $vehicle = DB::table('vehicles')->find($request->vehicle_id);
                if (!$vehicle || $vehicle->customer_id !== $customerRecord->id) {
                    return back()->withInput()->withErrors(['vehicle_id' => 'Invalid vehicle selection.']);
                }

                $order = RepairOrder::create([
                    'customer_id'          => $customerRecord->id,
                    'vehicle_id'           => $request->vehicle_id,
                    'service_advisor_name' => '',
                    'order_date'           => $request->order_date,
                    'status'               => 'open',
                    'notes'                => $request->notes,
                    'created_by'           => session('user_id'),
                    'updated_by'           => session('user_id'),
                ]);
            } else {
                $request->validate([
                    'customer_id'          => ['required', 'exists:customers,id'],
                    'vehicle_id'           => ['required', 'exists:vehicles,id'],
                    'order_date'           => ['required', 'date'],
                    'notes'                => ['nullable', 'string'],
                    'service_ids'          => ['required', 'array', 'min:1'],
                    'service_ids.*'        => ['exists:service_types,id'],
                ]);

                $advisorName = session('first_name') . ' ' . session('last_name');

                $order = RepairOrder::create([
                    'customer_id'          => $request->customer_id,
                    'vehicle_id'           => $request->vehicle_id,
                    'service_advisor_name' => trim($advisorName),
                    'order_date'           => $request->order_date,
                    'status'               => 'open',
                    'notes'                => $request->notes,
                    'created_by'           => session('user_id'),
                    'updated_by'           => session('user_id'),
                ]);
            }

            foreach ($request->service_ids as $stId) {
                $st = DB::table('service_types')->find($stId);
                if (!$st) continue;
                RepairOrderService::create([
                    'repair_order_id' => $order->id,
                    'service_type_id' => $st->id,
                    'book_hours'      => $st->book_hours,
                    'rate_per_hour'   => $st->rate_per_hour,
                    'line_total'      => $st->book_hours * $st->rate_per_hour,
                ]);
            }

            $orderId = $order->id;
            AuditHelper::log('CREATE', 'repair_orders', $orderId, "Repair order created #{$orderId}");
            Log::info("Repair order created: #{$orderId}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create repair order.']);
        } finally {
            Log::debug("=================== end repair order create ===================");
        }
        return redirect()->route('repair-orders.index');
    }

    public function show($id)
    {
        $order = null; $services = collect();
        try {
            Log::debug("=================== repair order detail ===================");
            $baseQuery = DB::table('repair_orders')
                ->join('customers', 'repair_orders.customer_id', 'customers.id')
                ->join('vehicles', 'repair_orders.vehicle_id', 'vehicles.id')
                ->select(
                    'repair_orders.*',
                    'customers.first_name as cust_first',
                    'customers.last_name as cust_last',
                    'customers.phone as cust_phone',
                    'customers.email as cust_email',
                    'vehicles.make',
                    'vehicles.model',
                    'vehicles.year',
                    'vehicles.license_plate',
                    'vehicles.vin'
                );

            if (session('role') === 'customer') {
                $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
                if ($customer) {
                    $baseQuery->where('repair_orders.customer_id', $customer->id);
                }
            }

            $order = $baseQuery->where('repair_orders.id', $id)->first();

            if (!$order) return redirect()->route('repair-orders.index');

            $services = DB::table('repair_order_services')
                ->join('service_types', 'repair_order_services.service_type_id', 'service_types.id')
                ->select(
                    'repair_order_services.*',
                    'service_types.name as service_name'
                )
                ->where('repair_order_services.repair_order_id', $id)
                ->get();

            Log::info("Repair order detail: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end repair order detail ===================");
        }
        return view('RepairOrder.show', compact('order', 'services'));
    }

    public function edit($id)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        $order = null; $customers = collect(); $vehicles = collect(); $serviceTypes = collect(); $services = collect();
        try {
            Log::debug("=================== repair order edit form ===================");
            $order = DB::table('repair_orders')->find($id);
            if (!$order) return redirect()->route('repair-orders.index');

            $customers = DB::table('customers')->orderBy('last_name')->get();
            $vehicles = DB::table('vehicles')->where('customer_id', $order->customer_id)->orderBy('make')->get();
            $serviceTypes = DB::table('service_types')->orderBy('name')->get();

            $services = DB::table('repair_order_services')
                ->join('service_types', 'repair_order_services.service_type_id', 'service_types.id')
                ->select(
                    'repair_order_services.*',
                    'service_types.name as service_name'
                )
                ->where('repair_order_services.repair_order_id', $id)
                ->get();
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end repair order edit form ===================");
        }
        return view('RepairOrder.edit', compact('order', 'customers', 'vehicles', 'serviceTypes', 'services'));
    }

    public function update(Request $request, $id)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        // Handle "Add Service" action
        if ($request->has('add_service') && $request->filled('add_service_id')) {
            try {
                Log::debug("=================== repair order add service ===================");
                $st = DB::table('service_types')->find($request->add_service_id);
                if ($st) {
                    RepairOrderService::create([
                        'repair_order_id' => $id,
                        'service_type_id' => $st->id,
                        'book_hours'      => $st->book_hours,
                        'rate_per_hour'   => $st->rate_per_hour,
                        'line_total'      => $st->book_hours * $st->rate_per_hour,
                    ]);
                    AuditHelper::log('CREATE', 'repair_order_services', null, "Service '{$st->name}' added to order #{$id}");
                    Log::info("Service added to order #{$id}: {$st->name}");
                }
            } catch (Throwable $error) {
                Log::error($error->getMessage());
            } finally {
                Log::debug("=================== end repair order add service ===================");
            }
            return redirect()->route('repair-orders.edit', $id);
        }

        // Handle "Update Order" action
        try {
            Log::debug("=================== repair order update ===================");
            $request->validate([
                'customer_id'          => ['required', 'exists:customers,id'],
                'vehicle_id'           => ['required', 'exists:vehicles,id'],
                'service_advisor_name' => ['required', 'max:100'],
                'order_date'           => ['required', 'date'],
                'status'               => ['required', 'in:open,in_progress,completed,cancelled'],
                'notes'                => ['nullable', 'string'],
            ]);

            $current = RepairOrder::findOrFail($id);
            if (!$current->canTransitionTo($request->status)) {
                return back()->withInput()->withErrors(['status' => "Cannot change status from '{$current->status}' to '{$request->status}'."]);
            }

            $current->update([
                'customer_id'          => $request->customer_id,
                'vehicle_id'           => $request->vehicle_id,
                'service_advisor_name' => $request->service_advisor_name,
                'order_date'           => $request->order_date,
                'status'               => $request->status,
                'notes'                => $request->notes,
                'updated_by'           => session('user_id'),
            ]);

            AuditHelper::log('UPDATE', 'repair_orders', $id, "Repair order updated #{$id}");
            Log::info("Repair order updated: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to update repair order.']);
        } finally {
            Log::debug("=================== end repair order update ===================");
        }
        return redirect()->route('repair-orders.show', $id);
    }

    public function destroy($id)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        try {
            Log::debug("=================== repair order delete ===================");
            $order = RepairOrder::find($id);
            if (!$order) return redirect()->route('repair-orders.index');
            $order->services()->delete();
            $order->delete();
            AuditHelper::log('DELETE', 'repair_orders', $id, "Repair order deleted #{$id}");
            Log::info("Repair order deleted: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end repair order delete ===================");
        }
        return redirect()->route('repair-orders.index');
    }

    public function removeService($id, $serviceId)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        try {
            Log::debug("=================== repair order remove service ===================");
            $service = RepairOrderService::where('repair_order_id', $id)
                ->where('id', $serviceId)
                ->first();
            if ($service) {
                $service->delete();
                AuditHelper::log('DELETE', 'repair_order_services', $serviceId, "Service removed from order #{$id}");
            }
            Log::info("Service removed from order #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end repair order remove service ===================");
        }
        return redirect()->route('repair-orders.edit', $id);
    }

    public function getByCustomer($customerId)
    {
        try {
            // Customers can only fetch their own vehicles
            if (session('role') === 'customer') {
                $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
                if (!$customer || (int)$customer->id !== (int)$customerId) {
                    return response()->json([]);
                }
            }

            $vehicles = DB::table('vehicles')
                ->where('customer_id', $customerId)
                ->orderBy('make')
                ->get();
            return response()->json($vehicles);
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return response()->json([]);
        }
    }
}
