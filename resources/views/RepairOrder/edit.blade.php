@extends('Template.main')
@section('title', 'Edit Repair Order')
@section('content')
<div class="page-wrapper-md">
    <div class="mb-6">
        <a href="{{ route('repair-orders.index') }}" class="link-back">&larr; Back to Orders</a>
    </div>
    <h1 class="page-title">Repair Order #{{ $order->id }}</h1>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('repair-orders.update', $order->id) }}" class="card-form">
        @csrf @method('PUT')
        <div class="grid-2-responsive">
            <div>
                <label for="customer_id" class="form-label">Customer</label>
                <select name="customer_id" id="customer_id" class="form-input" required>
                    <option value="">Select customer</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ $order->customer_id == $c->id ? 'selected' : '' }}>{{ $c->last_name }}, {{ $c->first_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="vehicle_id" class="form-label">Vehicle</label>
                <select name="vehicle_id" id="vehicle_id" class="form-input" required>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" {{ $order->vehicle_id == $v->id ? 'selected' : '' }}>{{ $v->year }} {{ $v->make }} {{ $v->model }} ({{ $v->license_plate ?? 'no plate' }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid-2-responsive">
            <div>
                <label for="service_advisor_name" class="form-label">Service Advisor</label>
                <input type="text" name="service_advisor_name" id="service_advisor_name" value="{{ $order->service_advisor_name }}" class="form-input" required>
            </div>
            <div>
                <label for="order_date" class="form-label">Order Date</label>
                <input type="date" name="order_date" id="order_date" value="{{ $order->order_date }}" class="form-input" required>
            </div>
        </div>
        <div>
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-input">
                <option value="open" {{ $order->status == 'open' ? 'selected' : '' }}>Open</option>
                <option value="in_progress" {{ $order->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>
        <div>
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" id="notes" rows="3" class="form-input">{{ $order->notes }}</textarea>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Line Items</h2>
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="py-2.5 px-4 font-semibold text-gray-600">Service</th>
                            <th class="py-2.5 px-4 font-semibold text-gray-600">Hours</th>
                            <th class="py-2.5 px-4 font-semibold text-gray-600">Rate</th>
                            <th class="py-2.5 px-4 font-semibold text-gray-600">Total</th>
                            <th class="py-2.5 px-4 font-semibold text-gray-600"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($services as $s)
                        <tr>
                            <td class="py-2.5 px-4">{{ $s->service_name }}</td>
                            <td class="py-2.5 px-4 text-gray-600">{{ $s->book_hours }}</td>
                            <td class="py-2.5 px-4 text-gray-600">${{ number_format($s->rate_per_hour, 2) }}</td>
                            <td class="py-2.5 px-4 text-gray-600">${{ number_format($s->line_total, 2) }}</td>
                            <td class="py-2.5 px-4">
                                <a href="{{ route('repair-orders.remove-service', [$order->id, $s->id]) }}" class="text-red-600 hover:text-red-800 text-xs font-medium" onclick="return confirm('Remove this service?')">Remove</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-right text-sm font-semibold text-gray-800 mt-2">Total: ${{ number_format($services->sum('line_total'), 2) }}</p>
        </div>

        <div>
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Add Service</h2>
            <div class="flex gap-2">
                <select name="add_service_id" class="form-input flex-1">
                    <option value="">Select service to add</option>
                    @foreach ($serviceTypes as $st)
                        <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->book_hours }}h @ ${{ number_format($st->rate_per_hour, 2) }}/hr)</option>
                    @endforeach
                </select>
                <button type="submit" name="add_service" value="1" class="btn-success">Add</button>
            </div>
        </div>

        <div class="btn-row border-t border-gray-100">
            <button type="submit" name="update_order" value="1" class="btn-primary">Update Order</button>
            <a href="{{ route('repair-orders.show', $order->id) }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
