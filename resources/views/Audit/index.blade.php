@extends('Template.main')
@section('title', 'Audit Trail')
@section('content')
<div class="page-index">
    <h1 class="page-title">Audit Trail</h1>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card-filter">
        <form method="GET" action="{{ route('audit.index') }}" class="flex flex-wrap gap-4 items-end">
            <div>
                <label for="entity_type" class="block text-xs font-medium text-gray-600 mb-1">Entity Type</label>
                <select name="entity_type" id="entity_type" class="form-input">
                    <option value="">All</option>
                    <option value="customers" {{ request('entity_type') == 'customers' ? 'selected' : '' }}>Customer</option>
                    <option value="vehicles" {{ request('entity_type') == 'vehicles' ? 'selected' : '' }}>Vehicle</option>
                    <option value="repair_orders" {{ request('entity_type') == 'repair_orders' ? 'selected' : '' }}>Repair Order</option>
                    <option value="repair_order_services" {{ request('entity_type') == 'repair_order_services' ? 'selected' : '' }}>Service Line</option>
                    <option value="service_types" {{ request('entity_type') == 'service_types' ? 'selected' : '' }}>Service Type</option>
                    <option value="users" {{ request('entity_type') == 'users' ? 'selected' : '' }}>User</option>
                    <option value="auth" {{ request('entity_type') == 'auth' ? 'selected' : '' }}>Auth</option>
                    <option value="role_user" {{ request('entity_type') == 'role_user' ? 'selected' : '' }}>Role Assignment</option>
                </select>
            </div>
            <div>
                <label for="action" class="block text-xs font-medium text-gray-600 mb-1">Action</label>
                <select name="action" id="action" class="form-input">
                    <option value="">All</option>
                    <option value="CREATE" {{ request('action') == 'CREATE' ? 'selected' : '' }}>Create</option>
                    <option value="UPDATE" {{ request('action') == 'UPDATE' ? 'selected' : '' }}>Update</option>
                    <option value="DELETE" {{ request('action') == 'DELETE' ? 'selected' : '' }}>Delete</option>
                    <option value="LOGIN" {{ request('action') == 'LOGIN' ? 'selected' : '' }}>Login</option>
                    <option value="LOGOUT" {{ request('action') == 'LOGOUT' ? 'selected' : '' }}>Logout</option>
                </select>
            </div>
            <div>
                <label for="user_id" class="block text-xs font-medium text-gray-600 mb-1">User</label>
                <select name="user_id" id="user_id" class="form-input">
                    <option value="">All</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->first_name }} {{ $u->last_name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary-sm">Filter</button>
            <a href="{{ route('audit.index') }}" class="btn-secondary-sm">Clear</a>
        </form>
    </div>

    <div class="card-table">
        <div class="table-scroll">
            <table class="table-standard">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="th-cell">Date/Time</th>
                        <th class="th-cell">User</th>
                        <th class="th-cell">Entity</th>
                        <th class="th-cell">Action</th>
                        <th class="th-cell">Record</th>
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($logs as $log)
                        @php
                            $rowTints = [
                                'CREATE' => 'tr-row-create',
                                'UPDATE' => 'tr-row-update',
                                'DELETE' => 'tr-row-delete',
                                'LOGIN'  => 'tr-row-login',
                                'LOGOUT' => 'tr-row-logout',
                            ];
                            $rowClass = $rowTints[$log->action] ?? '';
                        @endphp
                        <tr class="tr-hover {{ $rowClass }}">
                             <td class="td-secondary whitespace-nowrap td-border-{{ strtolower($log->action) }}">{{ $log->created_at }}</td>
                             <td class="td-primary">{{ $log->first_name }} {{ $log->last_name }}</td>
                             <td class="td-cell">
                                 {{ str_replace('_', ' ', ucfirst($log->entity_type)) }}
                             </td>
                             <td class="td-cell">
                                 @php
                                     $actionTextColors = [
                                         'CREATE' => 'text-green-700',
                                         'UPDATE' => 'text-yellow-700',
                                         'DELETE' => 'text-red-700',
                                         'LOGIN'  => 'text-purple-700',
                                         'LOGOUT' => 'text-gray-600',
                                     ];
                                     $textColor = $actionTextColors[$log->action] ?? '';
                                 @endphp
                                 <span class="font-semibold {{ $textColor }}">{{ $log->action }}</span>
                             </td>
                            <td class="td-cell">
                                <span class="text-gray-500 text-xs">#{{ $log->entity_id }} ({{ $log->entity_identifier ?? '—' }})</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="td-empty">No audit entries found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-sm text-gray-500">
        Showing {{ $logs->firstItem() ?? 0 }} – {{ $logs->lastItem() ?? 0 }} of {{ $logs->total() }} entries
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
