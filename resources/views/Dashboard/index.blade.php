@extends('Template.main')
@section('title', 'Dashboard')
@section('content')
<div class="page-index">

    {{-- Header with greeting --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1">
                <i data-lucide="calendar" class="inline w-3 h-3 align-middle"></i>
                {{ now()->format('l, F j, Y') }}
                &middot;
                Welcome back, <span class="font-medium text-slate-700">{{ session('first_name') }}</span>
            </p>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- METRICS STRIP -- no card borders, no backgrounds, just icon + number + label --}}
    <div class="metrics-strip">
        <div class="metrics-item">
            <span class="metrics-icon" style="background:#eff6ff;color:#1e40af"><i data-lucide="users"></i></span>
            <div class="metrics-body">
                <span class="metrics-number">{{ $customerCount }}</span>
                <span class="metrics-label">Customers</span>
            </div>
        </div>
        <div class="metrics-item">
            <span class="metrics-icon" style="background:#fef3c7;color:#d97706"><i data-lucide="truck"></i></span>
            <div class="metrics-body">
                <span class="metrics-number">{{ $vehicleCount }}</span>
                <span class="metrics-label">Vehicles</span>
            </div>
        </div>
        <div class="metrics-item">
            <span class="metrics-icon" style="background:#fef3c7;color:#d97706"><i data-lucide="clipboard-list"></i></span>
            <div class="metrics-body">
                <span class="metrics-number">{{ $openOrders }}</span>
                <span class="metrics-label">Open Orders</span>
            </div>
        </div>
        <div class="metrics-item">
            <span class="metrics-icon" style="background:#f5f5f4;color:#57534e"><i data-lucide="badge-check"></i></span>
            <div class="metrics-body">
                <span class="metrics-number">{{ $completedOrders }}</span>
                <span class="metrics-label">Completed</span>
            </div>
        </div>
        <div class="metrics-item">
            <span class="metrics-icon" style="background:#f1f5f9;color:#475569"><i data-lucide="calendar-clock"></i></span>
            <div class="metrics-body">
                <span class="metrics-number">{{ $appointmentCount }}</span>
                <span class="metrics-label">Appointments</span>
            </div>
        </div>
        <div class="metrics-item">
            <span class="metrics-icon" style="background:#f5f5f4;color:#57534e"><i data-lucide="package"></i></span>
            <div class="metrics-body">
                <span class="metrics-number">{{ $supplyCount }}</span>
                <span class="metrics-label">Supplies</span>
            </div>
        </div>
        <div class="metrics-item">
            <span class="metrics-icon" style="background:#fef2f2;color:#dc2626"><i data-lucide="triangle-alert"></i></span>
            <div class="metrics-body">
                <span class="metrics-number {{ $lowStockCount > 0 ? 'text-red-600' : '' }}">{{ $lowStockCount }}</span>
                <span class="metrics-label">Low Stock</span>
            </div>
        </div>
        <div class="metrics-item">
            <span class="metrics-icon" style="background:#f5f5f4;color:#57534e"><i data-lucide="circle-dollar-sign"></i></span>
            <div class="metrics-body">
                <span class="metrics-number">${{ number_format($totalRevenue, 0) }}</span>
                <span class="metrics-label">Revenue</span>
            </div>
        </div>
    </div>

    {{-- TODAY'S ACTIVITY -- compact text strip, no cards --}}
    @php
        $today = now()->toDateString();
        $ordersToday = DB::table('repair_orders')->whereDate('created_at', $today)->count();
        $apptsToday  = DB::table('appointments')->where('appointment_date', $today)->count();
        $customersToday = DB::table('customers')->whereDate('created_at', $today)->count();
        $vehiclesToday  = DB::table('vehicles')->whereDate('created_at', $today)->count();
    @endphp
    <div class="section-heading">
        <i data-lucide="activity"></i>
        <h2>Today's Activity</h2>
    </div>
    <div class="flex flex-wrap items-center gap-x-5 gap-y-1 border-b border-slate-200 pb-3 mb-6 text-sm text-slate-600">
        <span><i data-lucide="file-text" class="inline w-3.5 h-3.5 align-middle mr-1 text-amber-600"></i> {{ $ordersToday }} order{{ $ordersToday !== 1 ? 's' : '' }} opened today</span>
        <span><i data-lucide="user-plus" class="inline w-3.5 h-3.5 align-middle mr-1 text-blue-700"></i> {{ $customersToday }} new customer{{ $customersToday !== 1 ? 's' : '' }}</span>
        <span><i data-lucide="truck" class="inline w-3.5 h-3.5 align-middle mr-1 text-amber-600"></i> {{ $vehiclesToday }} vehicle{{ $vehiclesToday !== 1 ? 's' : '' }} registered</span>
        <span><i data-lucide="calendar-clock" class="inline w-3.5 h-3.5 align-middle mr-1 text-slate-500"></i> {{ $apptsToday }} appointment{{ $apptsToday !== 1 ? 's' : '' }} today</span>
    </div>

    {{-- ORDER PIPELINE -- visual flow from open to in_progress to completed + cancelled --}}
    <div class="section-heading">
        <i data-lucide="workflow"></i>
        <h2>Order Pipeline</h2>
    </div>
    <div class="pipeline-flow">
        <div class="pipeline-stage pipeline-stage-open">
            <div class="pipeline-icon"><i data-lucide="inbox"></i></div>
            <div class="pipeline-count">{{ DB::table('repair_orders')->where('status', 'open')->count() }}</div>
            <div class="pipeline-label">Open</div>
            <div class="pipeline-sub">Awaiting work</div>
        </div>
        <div class="pipeline-stage pipeline-stage-progress">
            <div class="pipeline-icon"><i data-lucide="cog"></i></div>
            <div class="pipeline-count">{{ DB::table('repair_orders')->where('status', 'in_progress')->count() }}</div>
            <div class="pipeline-label">In Progress</div>
            <div class="pipeline-sub">Being serviced</div>
        </div>
        <div class="pipeline-stage pipeline-stage-done">
            <div class="pipeline-icon"><i data-lucide="badge-check"></i></div>
            <div class="pipeline-count">{{ DB::table('repair_orders')->where('status', 'completed')->count() }}</div>
            <div class="pipeline-label">Completed</div>
            <div class="pipeline-sub">Ready for pickup</div>
        </div>
        <div class="pipeline-stage pipeline-stage-cancelled">
            <div class="pipeline-icon"><i data-lucide="ban"></i></div>
            <div class="pipeline-count">{{ DB::table('repair_orders')->where('status', 'cancelled')->count() }}</div>
            <div class="pipeline-label">Cancelled</div>
            <div class="pipeline-sub">Voided orders</div>
        </div>
    </div>

    {{-- UPCOMING APPOINTMENTS -- timeline with dot indicators --}}
    <div class="section-heading">
        <i data-lucide="calendar-check"></i>
        <h2>Upcoming Appointments</h2>
    </div>
    <div class="border border-slate-200 rounded-lg bg-white overflow-hidden mb-6">
        @if ($upcomingAppointments->count() > 0)
            <div class="timeline-list px-3">
                @foreach ($upcomingAppointments as $a)
                    <div class="timeline-item">
                        <span class="timeline-dot
                            @if($a->status === 'scheduled') timeline-dot-scheduled
                            @elseif($a->status === 'confirmed') timeline-dot-confirmed
                            @else timeline-dot-completed @endif">
                            <i data-lucide="clock"></i>
                        </span>
                        <div class="timeline-body">
                            <div class="timeline-row">
                                <span class="timeline-time">{{ \Carbon\Carbon::parse($a->appointment_time)->format('g:i A') }}</span>
                                <span class="timeline-customer">{{ $a->cust_first }} {{ $a->cust_last }}</span>
                                <span>
                                    <span class="badge-pill
                                        @if($a->status === 'scheduled') badge-yellow
                                        @elseif($a->status === 'confirmed') badge-blue
                                        @else badge-gray @endif">
                                        <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        {{ ucfirst($a->status) }}
                                    </span>
                                </span>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-vehicle">{{ $a->make }} {{ $a->model }} &middot; {{ $a->license_plate ?? '—' }}</span>
                                <span class="timeline-chevron"><i data-lucide="chevron-right"></i></span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="td-empty">
                <i data-lucide="calendar-check" class="inline w-5 h-5 text-slate-300 mb-2 block mx-auto"></i>
                No upcoming appointments.
            </div>
        @endif
    </div>

    {{-- RECENT ORDERS -- slim workbench table --}}
    <div class="section-heading">
        <i data-lucide="list-ordered"></i>
        <h2>Recent Orders</h2>
    </div>
    <div class="border border-slate-200 rounded-lg bg-white overflow-hidden">
        @if ($recentOrders->count() > 0)
            <div class="overflow-x-auto">
                <table class="table-workbench">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Plate</th>
                            <th>Advisor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentOrders as $o)
                            <tr>
                                <td class="td-mono text-slate-400">#{{ $o->id }}</td>
                                <td class="td-primary">{{ $o->cust_first }} {{ $o->cust_last }}</td>
                                <td>{{ $o->year }} {{ $o->make }} {{ $o->model }}</td>
                                <td class="text-slate-500">{{ $o->license_plate ?? '—' }}</td>
                                <td class="text-slate-500">{{ $o->service_advisor_name }}</td>
                                <td>
                                    <span class="badge-pill
                                        @if($o->status === 'open') badge-yellow
                                        @elseif($o->status === 'in_progress') badge-gray
                                        @elseif($o->status === 'completed') badge-green
                                        @elseif($o->status === 'cancelled') badge-red
                                        @else badge-gray @endif">
                                        <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        {{ str_replace('_', ' ', ucfirst($o->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="td-empty">
                <i data-lucide="clipboard-list" class="inline w-5 h-5 text-slate-300 mb-2 block mx-auto"></i>
                No orders yet.
            </div>
        @endif
    </div>
</div>
@endsection
