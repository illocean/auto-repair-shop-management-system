@extends('Template.main')
@section('title', 'Edit Repair Order')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Repair Order #{{ $order->id }}</h1>
        <div class="flex gap-3">
            <a href="{{ route('repair-orders.show', $order->id) }}" class="btn-secondary-sm">
                <i data-lucide="eye" class="w-4 h-4"></i>
                View
            </a>
            <a href="{{ route('repair-orders.index') }}" class="btn-secondary-sm">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Back
            </a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('repair-orders.update', $order->id) }}" class="card-form">
                @csrf @method('PUT')
                <div class="grid-2">
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
                <div class="grid-2">
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
                    <label class="form-label">Line Items</label>
                    <div class="table-scroll border border-slate-200 rounded-lg">
                        <table class="table-standard">
                            <thead>
                                <tr>
                                    <th class="th-cell">Service</th>
                                    <th class="th-cell">Hours</th>
                                    <th class="th-cell">Rate</th>
                                    <th class="th-cell">Total</th>
                                    <th class="th-cell"></th>
                                </tr>
                            </thead>
                            <tbody class="tbody-divide">
                                @foreach ($services as $s)
                                <tr class="tr-hover">
                                    <td class="td-primary">{{ $s->service_name }}</td>
                                    <td class="td-secondary">{{ $s->book_hours }}</td>
                                    <td class="td-secondary">${{ number_format($s->rate_per_hour, 2) }}</td>
                                    <td class="td-secondary">${{ number_format($s->line_total, 2) }}</td>
                                    <td class="td-cell">
                                        <a href="{{ route('repair-orders.remove-service', [$order->id, $s->id]) }}" class="link-danger text-sm" onclick="return confirm('Remove this service?')">Remove</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-semibold bg-slate-50">
                                    <td colspan="3" class="td-cell text-right">Total:</td>
                                    <td class="td-cell">${{ number_format($services->sum('line_total'), 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div>
                    <label for="add_service_id" class="form-label">Add Service</label>
                    <div class="flex gap-2">
                        <select name="add_service_id" id="add_service_id" class="form-input flex-1">
                            <option value="">Select service to add</option>
                            @foreach ($serviceTypes as $st)
                                <option value="{{ $st->id }}">{{ $st->name }} ({{ $st->book_hours }}h @ ${{ number_format($st->rate_per_hour, 2) }}/hr)</option>
                            @endforeach
                        </select>
                        <button type="submit" name="add_service" value="1" class="btn-primary-sm">Add</button>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" name="update_order" value="1" class="btn-primary-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Update Order
                    </button>
                    <a href="{{ route('repair-orders.show', $order->id) }}" class="btn-secondary-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
