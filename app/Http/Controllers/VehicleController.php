<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Helpers\AuditHelper;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = collect(); $customers = collect();
        try {
            Log::debug("=================== vehicle list ===================");
            $query = DB::table('vehicles')
                ->join('customers', 'vehicles.customer_id', 'customers.id')
                ->select('vehicles.*', 'customers.first_name as cust_first', 'customers.last_name as cust_last');

            if (session('role') === 'customer') {
                $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
                if ($customer) {
                    $query->where('vehicles.customer_id', $customer->id);
                    $customers = collect([$customer]);
                }
            } else {
                $customers = DB::table('customers')->orderBy('last_name')->get();
                if ($request->filled('customer_id')) {
                    $query->where('vehicles.customer_id', $request->customer_id);
                }
            }

            $vehicles = $query->orderBy('vehicles.created_at', 'desc')->get();
            Log::info("Vehicle list: " . count($vehicles) . " records");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end vehicle list ===================");
        }
        return view('Vehicle.index', compact('vehicles', 'customers'));
    }

    public function create()
    {
        $customers = collect();
        try {
            Log::debug("=================== vehicle create form ===================");
            // Customers don't need the owner dropdown — it's always them
            if (session('role') !== 'customer') {
                $customers = DB::table('customers')->orderBy('last_name')->get();
            }
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end vehicle create form ===================");
        }
        return view('Vehicle.create', compact('customers'));
    }

    public function store(VehicleRequest $request)
    {
        try {
            Log::debug("=================== vehicle create ===================");

            $data = [
                'make'          => $request->make,
                'model'         => $request->model,
                'year'          => $request->year,
                'license_plate' => $request->license_plate,
                'vin'           => $request->vin,
            ];

            if (session('role') === 'customer') {
                $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
                if (!$customer) {
                    return back()->withInput()->withErrors(['error' => 'No customer profile found. Contact support.']);
                }
                $data['customer_id'] = $customer->id;
            } else {
                $request->validate(['customer_id' => 'required|exists:customers,id']);
                $data['customer_id'] = $request->customer_id;
            }

            $vehicle = Vehicle::create($data);
            AuditHelper::log('CREATE', 'vehicles', $vehicle->id, "Vehicle created: {$request->make} {$request->model}");
            Log::info("Vehicle created: {$request->make} {$request->model}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create vehicle.']);
        } finally {
            Log::debug("=================== end vehicle create ===================");
        }
        return redirect()->route('vehicles.index');
    }

    public function edit($id)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        $vehicle = null; $customers = collect();
        try {
            Log::debug("=================== vehicle edit ===================");
            $vehicle = DB::table('vehicles')->find($id);
            if (!$vehicle) return redirect()->route('vehicles.index');
            $customers = DB::table('customers')->orderBy('last_name')->get();
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end vehicle edit ===================");
        }
        return view('Vehicle.edit', compact('vehicle', 'customers'));
    }

    public function update(VehicleRequest $request, $id)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        try {
            Log::debug("=================== vehicle update ===================");
            Vehicle::findOrFail($id)->update([
                'customer_id'   => $request->customer_id,
                'make'          => $request->make,
                'model'         => $request->model,
                'year'          => $request->year,
                'license_plate' => $request->license_plate,
                'vin'           => $request->vin,
            ]);
            AuditHelper::log('UPDATE', 'vehicles', $id, "Vehicle updated #{$id}");
            Log::info("Vehicle updated: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to update vehicle.']);
        } finally {
            Log::debug("=================== end vehicle update ===================");
        }
        return redirect()->route('vehicles.index');
    }

    public function destroy($id)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        try {
            Log::debug("=================== vehicle delete ===================");
            $vehicle = Vehicle::find($id);
            if (!$vehicle) return redirect()->route('vehicles.index');
            $vehicle->delete();
            AuditHelper::log('DELETE', 'vehicles', $id, "Vehicle deleted #{$id}");
            Log::info("Vehicle deleted: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end vehicle delete ===================");
        }
        return redirect()->route('vehicles.index');
    }
}
