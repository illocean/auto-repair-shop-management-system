@extends('Template.main')
@section('title', 'Book Appointment')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Book Appointment</h1>
        <a href="{{ route('appointments.index') }}" class="link-back">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
        </a>
    </div>

    <div class="page-wrapper-lg">
        <form method="POST" action="{{ route('appointments.store') }}" class="card-form">
            @csrf

            <div>
                <label class="form-label" for="customer_id">Customer</label>
                <select name="customer_id" id="customer_id" class="form-select" required>
                    <option value="">Select customer…</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                            {{ $c->last_name }}, {{ $c->first_name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label" for="vehicle_id">Vehicle</label>
                <select name="vehicle_id" id="vehicle_id" class="form-select" required>
                    <option value="">Select customer first…</option>
                </select>
                @error('vehicle_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid-2">
                <div>
                    <label class="form-label" for="appointment_date">Date</label>
                    <input type="date" name="appointment_date" id="appointment_date"
                           class="form-input" value="{{ old('appointment_date') }}" min="{{ now()->format('Y-m-d') }}" required>
                    @error('appointment_date') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label" for="appointment_time">Time</label>
                    <input type="time" name="appointment_time" id="appointment_time"
                           class="form-input" value="{{ old('appointment_time', '09:00') }}" required>
                    @error('appointment_time') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="form-label" for="notes">Notes (optional)</label>
                <textarea name="notes" id="notes" class="form-textarea" rows="3">{{ old('notes') }}</textarea>
                @error('notes') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Book Appointment</button>
                <a href="{{ route('appointments.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const customerSelect = document.getElementById('customer_id');
    const vehicleSelect = document.getElementById('vehicle_id');

    function loadVehicles(customerId, selectedId) {
        if (!customerId) {
            vehicleSelect.innerHTML = '<option value="">Select customer first…</option>';
            return;
        }
        fetch('{{ url('appointments') }}/' + customerId + '/vehicles')
            .then(r => r.json())
            .then(data => {
                vehicleSelect.innerHTML = '<option value="">Select vehicle…</option>';
                data.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = v.year + ' ' + v.make + ' ' + v.model + ' (' + (v.license_plate || '—') + ')';
                    if (String(v.id) === String(selectedId)) opt.selected = true;
                    vehicleSelect.appendChild(opt);
                });
            });
    }

    @if (session('role') === 'customer')
        // Auto-load vehicles for customer (they only see their own)
        loadVehicles('{{ $customers->first()->id ?? '' }}', '{{ old('vehicle_id') }}');
    @else
        customerSelect.addEventListener('change', function () {
            loadVehicles(this.value, '');
        });
        // Load on page load if customer already selected (edit/validation)
        @if (old('customer_id'))
            loadVehicles('{{ old('customer_id') }}', '{{ old('vehicle_id') }}');
        @endif
    @endif
});
</script>
@endsection
