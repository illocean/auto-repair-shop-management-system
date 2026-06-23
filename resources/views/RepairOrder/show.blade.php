@extends('Template.main')
@section('title', 'Repair Order #' . $order->id)
@section('content')
<div class="page-wrapper-md">
    <div class="page-header">
        <div>
            <a href="{{ route('repair-orders.index') }}" class="link-back">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back to Orders
            </a>
            <h1 class="page-title mt-1">Repair Order #{{ $order->id }}</h1>
        </div>
        @if (session('role') !== 'customer')
        <a href="{{ route('repair-orders.edit', $order->id) }}" class="btn-primary-sm">
            <i data-lucide="pencil" class="w-4 h-4"></i>
            Edit
        </a>
        @endif
    </div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="detail-grid">
        <div class="card">
            <div class="card-header">
                <i data-lucide="user" class="w-4 h-4 text-indigo-500"></i>
                <h2 class="card-title">Customer &amp; Vehicle</h2>
            </div>
            <div class="card-body">
                <div class="detail-section">
                    <div class="detail-row">
                        <span class="detail-label">Customer</span>
                        <span class="detail-value">{{ $order->cust_first }} {{ $order->cust_last }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Vehicle</span>
                        <span class="detail-value">{{ $order->year }} {{ $order->make }} {{ $order->model }} ({{ $order->license_plate ?? 'no plate' }})</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i data-lucide="clipboard-list" class="w-4 h-4 text-indigo-500"></i>
                <h2 class="card-title">Order Details</h2>
            </div>
            <div class="card-body">
                <div class="detail-section">
                    <div class="detail-row">
                        <span class="detail-label">Service Advisor</span>
                        <span class="detail-value">{{ $order->service_advisor_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Order Date</span>
                        <span class="detail-value">{{ $order->order_date }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="badge-pill
                            @if ($order->status === 'open') badge-yellow
                            @elseif($order->status === 'in_progress') badge-gray
                            @elseif($order->status === 'completed') badge-green
                            @else badge-gray @endif">
                            <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3"/>
                            </svg>
                            {{ str_replace('_', ' ', ucfirst($order->status)) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        @if ($order->notes)
        <div class="card">
            <div class="card-header">
                <i data-lucide="message-square" class="w-4 h-4 text-indigo-500"></i>
                <h2 class="card-title">Notes</h2>
            </div>
            <div class="card-body">
                <p class="text-sm text-gray-700">{{ $order->notes }}</p>
            </div>
        </div>
        @endif
    </div>

    <div class="card-table">
        <div class="card-table-header">
            <i data-lucide="wrench" class="w-5 h-5 text-gray-400"></i>
            <h2 class="card-table-title">Services</h2>
        </div>
        <table class="table-standard">
            <thead>
                <tr>
                    <th class="th-cell">Service</th>
                    <th class="th-cell">Hours</th>
                    <th class="th-cell">Rate</th>
                    <th class="th-cell text-right">Total</th>
                </tr>
            </thead>
            <tbody class="tbody-divide">
                @php $grandTotal = 0; @endphp
                @foreach ($services as $s)
                    @php $grandTotal += $s->line_total; @endphp
                    <tr>
                        <td class="td-primary">{{ $s->service_name }}</td>
                        <td class="td-secondary">{{ $s->book_hours }}</td>
                        <td class="td-secondary">${{ number_format($s->rate_per_hour, 2) }}</td>
                        <td class="td-secondary text-right">${{ number_format($s->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-semibold bg-gray-50">
                    <td colspan="3" class="td-cell text-right">Total:</td>
                    <td class="td-cell text-right">${{ number_format($grandTotal, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
