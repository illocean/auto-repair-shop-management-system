<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceTypeRequest;
use App\Helpers\AuditHelper;
use App\Models\ServiceType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = collect();
        try {
            Log::debug("=================== service type list ===================");
            $serviceTypes = DB::table('service_types')->orderBy('name')->get();
            Log::info("Service types: " . count($serviceTypes) . " records");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end service type list ===================");
        }
        return view('ServiceType.index', compact('serviceTypes'));
    }

    public function create()
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403, 'Only managers and admins can create service types.');
        }

        return view('ServiceType.create');
    }

    public function store(ServiceTypeRequest $request)
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403);
        }

        try {
            Log::debug("=================== service type create ===================");
            $st = ServiceType::create([
                'name'          => $request->name,
                'description'   => $request->description,
                'book_hours'    => $request->book_hours,
                'rate_per_hour' => $request->rate_per_hour,
            ]);
            AuditHelper::log('CREATE', 'service_types', $st->id, "Service type created: {$request->name}");
            Log::info("Service type created: {$request->name}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create service type.']);
        } finally {
            Log::debug("=================== end service type create ===================");
        }
        return redirect()->route('service-types.index');
    }

    public function edit($id)
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403);
        }

        $serviceType = null;
        try {
            Log::debug("=================== service type edit ===================");
            $serviceType = DB::table('service_types')->find($id);
            if (!$serviceType) return redirect()->route('service-types.index');
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end service type edit ===================");
        }
        return view('ServiceType.edit', compact('serviceType'));
    }

    public function update(ServiceTypeRequest $request, $id)
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403);
        }

        try {
            Log::debug("=================== service type update ===================");
            ServiceType::findOrFail($id)->update([
                'name'          => $request->name,
                'description'   => $request->description,
                'book_hours'    => $request->book_hours,
                'rate_per_hour' => $request->rate_per_hour,
            ]);
            AuditHelper::log('UPDATE', 'service_types', $id, "Service type updated #{$id}");
            Log::info("Service type updated: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to update service type.']);
        } finally {
            Log::debug("=================== end service type update ===================");
        }
        return redirect()->route('service-types.index');
    }

    public function destroy($id)
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403);
        }

        try {
            Log::debug("=================== service type delete ===================");
            $serviceType = ServiceType::find($id);

            if (!$serviceType) return redirect()->route('service-types.index');

            $serviceType->delete();

            AuditHelper::log('DELETE', 'service_types', $id, "Service type deleted #{$id}");
            Log::info("Service type deleted: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end service type delete ===================");
        }
        return redirect()->route('service-types.index');
    }
}
