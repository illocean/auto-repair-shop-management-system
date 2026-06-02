@extends('Template.main')
@section('title', 'Dashboard')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Dashboard</h1>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid-4-stats">
        <div class="card-stat">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Customers</p>
            <p class="text-3xl font-bold text-gray-600 mt-1">{{ $customerCount }}</p>
        </div>
        <div class="card-stat">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicles</p>
            <p class="text-3xl font-bold text-indigo-600 mt-1">{{ $vehicleCount }}</p>
        </div>
        <div class="card-stat">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Open Orders</p>
            <p class="text-3xl font-bold text-yellow-600 mt-1">{{ $openOrders }}</p>
        </div>
        <div class="card-stat">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</p>
            <p class="text-3xl font-bold text-green-600 mt-1">{{ $completedOrders }}</p>
        </div>
    </div>

    <div class="grid-3-nav">
        <a href="{{ route('customers.index') }}" class="card-nav">
            <h2 class="font-semibold text-gray-800">Customers</h2>
            <p class="text-sm text-gray-500 mt-1">Manage customer records</p>
        </a>
        <a href="{{ route('vehicles.index') }}" class="card-nav">
            <h2 class="font-semibold text-gray-800">Vehicles</h2>
            <p class="text-sm text-gray-500 mt-1">View and manage vehicles</p>
        </a>
        <a href="{{ route('repair-orders.index') }}" class="card-nav">
            <h2 class="font-semibold text-gray-800">Repair Orders</h2>
            <p class="text-sm text-gray-500 mt-1">Create and manage work orders</p>
        </a>
    </div>

    <div class="card-table">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Recent Orders</h2>
        </div>
        @if ($recentOrders->count() > 0)
            <div class="table-scroll">
                <table class="table-standard">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="th-cell">#</th>
                            <th class="th-cell">Customer</th>
                            <th class="th-cell">Vehicle</th>
                            <th class="th-cell">Plate</th>
                            <th class="th-cell">Advisor</th>
                            <th class="th-cell">Status</th>
                        </tr>
                    </thead>
                    <tbody class="tbody-divide">
                        @foreach ($recentOrders as $o)
                            @php
                                $sColors = [
                                    'open'        => ['row' => 'tr-row-yellow', 'border' => 'td-border-yellow'],
                                    'in_progress' => ['row' => 'tr-row-gray',   'border' => 'td-border-gray'],
                                    'completed'   => ['row' => 'tr-row-green',  'border' => 'td-border-green'],
                                ];
                                $sc = $sColors[$o->status] ?? ['row' => '', 'border' => ''];
                            @endphp
                            <tr class="tr-hover {{ $sc['row'] }}">
                                <td class="td-secondary {{ $sc['border'] }}">{{ $o->id }}</td>
                                <td class="td-primary">{{ $o->cust_first }} {{ $o->cust_last }}</td>
                                <td class="td-cell">{{ $o->year }} {{ $o->make }} {{ $o->model }}</td>
                                <td class="td-secondary">{{ $o->license_plate ?? '—' }}</td>
                                <td class="td-cell">{{ $o->service_advisor_name }}</td>
                                <td class="td-cell">
                                    <span class="badge-pill
                                        @if ($o->status === 'open') badge-yellow
                                        @elseif($o->status === 'in_progress') badge-gray
                                        @elseif($o->status === 'completed') badge-green-dark
                                        @else badge-gray @endif">
                                        {{ str_replace('_', ' ', ucfirst($o->status)) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-5 text-sm text-gray-500">No orders yet.</div>
        @endif
    </div>
</div>
@endsection
