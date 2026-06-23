<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = collect();
        try {
            Log::debug("=================== appointment list ===================");

            $query = DB::table('appointments')
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
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc');

            if (session('role') === 'customer') {
                $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
                if ($customer) {
                    $query->where('appointments.customer_id', $customer->id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            $appointments = $query->get();
            Log::info("Appointment list: " . count($appointments) . " records");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end appointment list ===================");
        }
        return view('Appointment.index', compact('appointments'));
    }

    public function calendar()
    {
        $appointments = collect();
        try {
            $query = DB::table('appointments')
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
                ->orderBy('appointment_date')
                ->orderBy('appointment_time');

            if (session('role') === 'customer') {
                $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
                if ($customer) {
                    $query->where('appointments.customer_id', $customer->id);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

            $appointments = $query->get();
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        }

        // Group by date for the calendar
        $grouped = $appointments->groupBy(fn ($a) => $a->appointment_date);
        $month = request('month', now()->month);
        $year = request('year', now()->year);
        $carbon = Carbon::createFromDate($year, $month, 1);

        return view('Appointment.calendar', compact('grouped', 'month', 'year', 'carbon'));
    }

    public function create()
    {
        if (session('role') === 'customer') {
            $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
            if (!$customer) {
                abort(403, 'No customer profile linked to your account.');
            }
            $vehicles = DB::table('vehicles')->where('customer_id', $customer->id)->get();
            $customers = collect([$customer]);
            return view('Appointment.create', compact('customers', 'vehicles'));
        }

        $customers = DB::table('customers')->orderBy('last_name')->get();
        $vehicles = collect(); // loaded via JS on customer select
        return view('Appointment.create', compact('customers', 'vehicles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            Log::debug("=================== appointment create ===================");
            $appointment = Appointment::create([
                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'scheduled',
                'created_by' => session('user_id'),
            ]);
            AuditHelper::log('CREATE', 'appointments', (string) $appointment->id, "Appointment booked for #{$validated['customer_id']} on {$validated['appointment_date']}");
            Log::info("Appointment created: #{$appointment->id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create appointment.']);
        } finally {
            Log::debug("=================== end appointment create ===================");
        }
        return redirect()->route('appointments.index')->with('success', 'Appointment booked.');
    }

    public function edit($id)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        $appointment = null;
        try {
            Log::debug("=================== appointment edit ===================");
            $appointment = DB::table('appointments')->find($id);
            if (!$appointment) return redirect()->route('appointments.index');
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end appointment edit ===================");
        }

        $customers = DB::table('customers')->orderBy('last_name')->get();
        $vehicles = DB::table('vehicles')->where('customer_id', $appointment->customer_id)->get();
        return view('Appointment.edit', compact('appointment', 'customers', 'vehicles'));
    }

    public function update(Request $request, $id)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'required|in:scheduled,confirmed,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            Log::debug("=================== appointment update ===================");
            Appointment::findOrFail($id)->update([
                'customer_id' => $validated['customer_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ]);
            AuditHelper::log('UPDATE', 'appointments', (string) $id, "Appointment updated #{$id}");
            Log::info("Appointment updated: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to update appointment.']);
        } finally {
            Log::debug("=================== end appointment update ===================");
        }
        return redirect()->route('appointments.index')->with('success', 'Appointment updated.');
    }

    public function destroy($id)
    {
        if (session('role') === 'customer') {
            abort(403);
        }

        try {
            Log::debug("=================== appointment delete ===================");
            $appointment = Appointment::find($id);
            if (!$appointment) return redirect()->route('appointments.index');
            $appointment->delete();
            AuditHelper::log('DELETE', 'appointments', (string) $id, "Appointment deleted #{$id}");
            Log::info("Appointment deleted: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end appointment delete ===================");
        }
        return redirect()->route('appointments.index')->with('success', 'Appointment deleted.');
    }

    public function getByCustomer($customerId)
    {
        $vehicles = DB::table('vehicles')->where('customer_id', $customerId)->get();
        return response()->json($vehicles);
    }
}
