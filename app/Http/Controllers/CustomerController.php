<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Helpers\AuditHelper;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = collect();
        try {
            Log::debug("=================== customer list ===================");

            if (session('role') === 'customer') {
                $customer = DB::table('customers')->where('user_id', session('user_id'))->first();
                $customers = $customer ? collect([$customer]) : collect();
            } else {
                $customers = DB::table('customers')->orderBy('last_name')->get();
            }

            Log::info("Customer list: " . count($customers) . " records");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end customer list ===================");
        }
        return view('Customer.index', compact('customers'));
    }

    public function create()
    {
        if (session('role') === 'customer') {
            abort(403, 'Customers cannot create new customer records.');
        }
        return view('Customer.create');
    }

    public function store(CustomerRequest $request)
    {
        if (session('role') === 'customer') {
            abort(403, 'Customers cannot create new customer records.');
        }

        try {
            Log::debug("=================== customer create ===================");
            $customer = Customer::create([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'address'    => $request->address,
            ]);
            AuditHelper::log('CREATE', 'customers', $customer->id, "Customer created: {$request->first_name} {$request->last_name}");
            Log::info("Customer created: {$request->first_name} {$request->last_name}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create customer.']);
        } finally {
            Log::debug("=================== end customer create ===================");
        }
        return redirect()->route('customers.index');
    }

    public function edit($id)
    {
        if (session('role') === 'customer') {
            abort(403, 'Customers cannot edit customer records.');
        }

        $customer = null;
        try {
            Log::debug("=================== customer edit ===================");
            $customer = DB::table('customers')->find($id);
            if (!$customer) return redirect()->route('customers.index');
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end customer edit ===================");
        }
        return view('Customer.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, $id)
    {
        if (session('role') === 'customer') {
            abort(403, 'Customers cannot edit customer records.');
        }

        try {
            Log::debug("=================== customer update ===================");
            Customer::findOrFail($id)->update([
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'address'    => $request->address,
            ]);
            AuditHelper::log('UPDATE', 'customers', $id, "Customer updated #{$id}");
            Log::info("Customer updated: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to update customer.']);
        } finally {
            Log::debug("=================== end customer update ===================");
        }
        return redirect()->route('customers.index');
    }

    public function destroy($id)
    {
        if (session('role') === 'customer') {
            abort(403, 'Customers cannot delete customer records.');
        }

        try {
            Log::debug("=================== customer delete ===================");
            $customer = Customer::find($id);
            if (!$customer) return redirect()->route('customers.index');
            $customer->delete();
            AuditHelper::log('DELETE', 'customers', $id, "Customer deleted #{$id}");
            Log::info("Customer deleted: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end customer delete ===================");
        }
        return redirect()->route('customers.index');
    }
}
