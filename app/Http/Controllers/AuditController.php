<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $logs = collect(); $users = collect();
        try {
            Log::debug("=================== audit log ===================");

            $query = DB::table('audit_logs');

            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }
            if ($request->filled('entity_type')) {
                $query->where('entity_type', $request->entity_type);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            if ($request->filled('user_id')) {
                $query->where('audit_logs.user_id', $request->user_id);
            }

            $logs = $query
                ->leftJoin('users', 'audit_logs.user_id', 'users.id')
                ->select('audit_logs.*', 'users.first_name', 'users.last_name')
                ->orderBy('audit_logs.created_at', 'desc')
                ->paginate(50);

            $users = DB::table('users')->orderBy('email')->get();

            Log::info("Audit log: " . $logs->total() . " entries");
        } catch (Throwable $error) {
            Log::error($error->getMessage());
        } finally {
            Log::debug("=================== end audit log ===================");
        }

        return view('Audit.index', compact('logs', 'users'));
    }
}
