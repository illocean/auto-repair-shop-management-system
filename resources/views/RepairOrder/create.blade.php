@extends('Template.main')
@section('title', 'New Repair Order')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">New Repair Order</h1>
        <a href="{{ route('repair-orders.index') }}" class="btn-secondary-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            Back
        </a>
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
            <form method="POST" action="{{ route('repair-orders.store') }}" class="card-form">
                @csrf

                @if (session('role') === 'customer')
                    <div>
                        <label for="vehicle_id" class="form-label">Vehicle</label>
                        <select name="vehicle_id" id="vehicle_id" class="form-input" required>
                            <option value="">Select your vehicle</option>
                            @foreach ($vehicles as $v)
                                <option value="{{ $v->id }}" {{ old('vehicle_id') == $v->id ? 'selected' : '' }}>
                                    {{ $v->year }} {{ $v->make }} {{ $v->model }} ({{ $v->license_plate ?? 'no plate' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div class="grid-2">
                        <div>
                            <label for="customer_id" class="form-label">Customer</label>
                            <select name="customer_id" id="customer_id" class="form-input" required>
                                <option value="">Select customer</option>
                                @foreach ($customers as $c)
                                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->last_name }}, {{ $c->first_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="vehicle_id" class="form-label">Vehicle</label>
                            <select name="vehicle_id" id="vehicle_id" class="form-input" required>
                                <option value="">Select customer first</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div>
                            <label for="service_advisor_name" class="form-label">Service Advisor</label>
                            <input type="text" name="service_advisor_name" id="service_advisor_name"
                                   class="form-input" value="{{ session('first_name') }} {{ session('last_name') }}" required>
                        </div>
                        <div>
                            <label for="order_date" class="form-label">Order Date</label>
                            <input type="date" name="order_date" id="order_date"
                                   class="form-input" value="{{ old('order_date', date('Y-m-d')) }}" required>
                        </div>
                    </div>

                    <div>
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-input">
                            <option value="open" selected>Open</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                @endif

                @if (session('role') === 'customer')
                <div>
                    <label for="order_date" class="form-label">Order Date</label>
                    <input type="date" name="order_date" id="order_date"
                           class="form-input" value="{{ old('order_date', date('Y-m-d')) }}" required>
                </div>
                @endif

                <div>
                    <label class="form-label">Services</label>
                    <div class="space-y-2">
                        @foreach ($serviceTypes as $st)
                            <label class="flex items-start gap-3 p-3 border border-slate-200 rounded hover:border-slate-400 cursor-pointer">
                                <input type="checkbox" name="service_ids[]" value="{{ $st->id }}"
                                       {{ in_array($st->id, old('service_ids', [])) ? 'checked' : '' }}
                                       class="mt-1">
                                <div>
                                    <span class="font-medium text-slate-900">{{ $st->name }}</span>
                                    <span class="text-sm text-slate-500 ml-2">{{ $st->book_hours }}h @ ${{ number_format($st->rate_per_hour, 2) }}/hr</span>
                                    @if($st->description)
                                        <p class="text-xs text-slate-400 mt-1">{{ $st->description }}</p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="form-input">{{ old('notes') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn-primary-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Create Order
                    </button>
                    <a href="{{ route('repair-orders.index') }}" class="btn-secondary-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

@if (session('role') !== 'customer')
<script>
document.getElementById('customer_id')?.addEventListener('change', function() {
    const vehicleSelect = document.getElementById('vehicle_id');
    vehicleSelect.innerHTML = '<option value="">Loading...</option>';
    if (!this.value) {
        vehicleSelect.innerHTML = '<option value="">Select customer first</option>';
        return;
    }
    fetch('/repair-orders/' + this.value + '/vehicles')
        .then(r => r.json())
        .then(data => {
            vehicleSelect.innerHTML = '<option value="">Select vehicle</option>';
            data.forEach(v => {
                vehicleSelect.innerHTML += '<option value="' + v.id + '">' + v.year + ' ' + v.make + ' ' + v.model + ' (' + (v.license_plate || 'no plate') + ')</option>';
            });
        })
        .catch(() => {
            vehicleSelect.innerHTML = '<option value="">Error loading vehicles</option>';
        });
});
</script>
@endif
@endsection
