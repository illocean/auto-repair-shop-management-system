@extends('Template.main')
@section('title', 'Repair Order #' . $order->id)
@section('content')
<div class="page-wrapper-md">
    <div class="page-header mb-6">
        <div>
            <a href="{{ route('repair-orders.index') }}" class="link-back">&larr; Back to Orders</a>
            <h1 class="page-title mt-1">Repair Order #{{ $order->id }}</h1>
        </div>
        @if (session('role') !== 'customer')
        <a href="{{ route('repair-orders.edit', $order->id) }}" class="btn-primary-sm">Edit</a>
        @endif
    </div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card-stat">
        <div class="grid-2-responsive">
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</p>
                <p class="font-medium mt-1">{{ $order->cust_first }} {{ $order->cust_last }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Vehicle</p>
                <p class="font-medium mt-1">{{ $order->year }} {{ $order->make }} {{ $order->model }} ({{ $order->license_plate ?? 'no plate' }})</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Service Advisor</p>
                <p class="font-medium mt-1">{{ $order->service_advisor_name }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</p>
                <p class="font-medium mt-1">{{ $order->order_date }}</p>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</p>
                <span class="badge-pill mt-1
                    @if ($order->status === 'open') badge-yellow
                    @elseif($order->status === 'in_progress') badge-gray
                    @elseif($order->status === 'completed') badge-green
                    @else badge-gray @endif">
                    {{ str_replace('_', ' ', ucfirst($order->status)) }}
                </span>
            </div>
        </div>

        @if ($order->notes)
        <div class="mt-4 pt-4 border-t border-gray-100">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</p>
            <p class="text-sm text-gray-700 mt-1">{{ $order->notes }}</p>
        </div>
        @endif
    </div>

    <div class="card-table mt-6">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Services</h2>
        </div>
        <table class="table-standard">
            <thead>
                <tr class="bg-gray-50 text-left">
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
