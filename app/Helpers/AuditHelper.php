<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuditHelper
{
    /**
     * Log an action to the audit trail.
     */
    public static function log(
        string $action,
        string $entityType,
        ?string $entityId = null,
        ?string $summary = null,
        $oldValues = null,
        $newValues = null
    ): void {
        try {
            $userId = session('user_id');
            $fullName = trim((session('first_name') ?? '') . ' ' . (session('last_name') ?? '')) ?: 'system';

            DB::table('audit_logs')->insert([
                'user_id'    => $userId,
                'username'   => $fullName,
                'action'     => $action,
                'entity_type' => $entityType,
                'entity_id'  => (string) $entityId,
                'summary'    => $summary,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);

            Log::info("AUDIT: {$action} on {$entityType}#{$entityId} by {$fullName}");
        } catch (Throwable $e) {
            Log::error('AuditHelper failed: ' . $e->getMessage());
        }
    }

    /**
     * Log a login/logout event.
     */
    public static function logAuth(string $action): void
    {
        self::log($action, 'auth', null, "User {$action}");
    }
}
