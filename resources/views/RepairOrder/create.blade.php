@extends('Template.main')
@section('title', 'New Repair Order')
@section('content')
<div class="page-wrapper-md">
    <div class="mb-6">
        <a href="{{ route('repair-orders.index') }}" class="link-back">&larr; Back to Orders</a>
    </div>
    <h1 class="page-title">New Repair Order</h1>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('repair-orders.store') }}" class="card-form">
        @csrf

        @if (session('role') === 'customer')
            {{-- Customer: hidden customer_id, vehicles loaded directly --}}
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
            {{-- Staff: customer + vehicle dropdowns with AJAX --}}
            <div class="grid-2-responsive">
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

            <div class="grid-2-responsive">
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

        {{-- Customer: just the order date --}}
        @if (session('role') === 'customer')
        <div>
            <label for="order_date" class="form-label">Order Date</label>
            <input type="date" name="order_date" id="order_date"
                   class="form-input" value="{{ old('order_date', date('Y-m-d')) }}" required>
        </div>
        @endif

        {{-- Services -- always shown --}}
        <div>
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Services</h2>
            <div class="space-y-2">
                @foreach ($serviceTypes as $st)
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                        <input type="checkbox" name="service_ids[]" value="{{ $st->id }}"
                               {{ in_array($st->id, old('service_ids', [])) ? 'checked' : '' }}
                               class="mt-0.5 h-4 w-4 text-gray-600 rounded">
                        <div>
                            <span class="font-medium text-gray-800 text-sm">{{ $st->name }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $st->book_hours }}h @ ${{ number_format($st->rate_per_hour, 2) }}/hr</span>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $st->description }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Notes --}}
        <div>
            <label for="notes" class="form-label">Notes</label>
            <textarea name="notes" id="notes" rows="3" class="form-input">{{ old('notes') }}</textarea>
        </div>

        <div class="btn-row">
            <button type="submit" class="btn-primary">Create Order</button>
            <a href="{{ route('repair-orders.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
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
