<?php

namespace App\Http\Controllers;

use App\Helpers\AuditHelper;
use App\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SupplyController extends Controller
{
    public function index()
    {
        $supplies = collect();
        try {
            Log::debug("=================== supply list ===================");
            $supplies = DB::table('supplies')->orderBy('name')->get();
            Log::info("Supply list: " . count($supplies) . " records");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end supply list ===================");
        }
        return view('Supply.index', compact('supplies'));
    }

    public function create()
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403, 'Only admins and managers can add supplies.');
        }
        return view('Supply.create');
    }

    public function store(Request $request)
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        try {
            Log::debug("=================== supply create ===================");
            $supply = Supply::create($validated);
            AuditHelper::log('CREATE', 'supplies', (string) $supply->id, "Supply created: {$validated['name']}");
            Log::info("Supply created: {$validated['name']}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to create supply.']);
        } finally {
            Log::debug("=================== end supply create ===================");
        }
        return redirect()->route('supplies.index')->with('success', 'Supply added.');
    }

    public function edit($id)
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403);
        }

        $supply = null;
        try {
            Log::debug("=================== supply edit ===================");
            $supply = DB::table('supplies')->find($id);
            if (!$supply) return redirect()->route('supplies.index');
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end supply edit ===================");
        }
        return view('Supply.edit', compact('supply'));
    }

    public function update(Request $request, $id)
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'quantity' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        try {
            Log::debug("=================== supply update ===================");
            Supply::findOrFail($id)->update($validated);
            AuditHelper::log('UPDATE', 'supplies', (string) $id, "Supply updated #{$id}");
            Log::info("Supply updated: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
            return back()->withInput()->withErrors(['error' => 'Failed to update supply.']);
        } finally {
            Log::debug("=================== end supply update ===================");
        }
        return redirect()->route('supplies.index')->with('success', 'Supply updated.');
    }

    public function destroy($id)
    {
        if (!in_array(session('role'), ['admin', 'manager'])) {
            abort(403);
        }

        try {
            Log::debug("=================== supply delete ===================");
            $supply = Supply::find($id);
            if (!$supply) return redirect()->route('supplies.index');
            $supply->delete();
            AuditHelper::log('DELETE', 'supplies', (string) $id, "Supply deleted #{$id}");
            Log::info("Supply deleted: #{$id}");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end supply delete ===================");
        }
        return redirect()->route('supplies.index')->with('success', 'Supply deleted.');
    }
}
