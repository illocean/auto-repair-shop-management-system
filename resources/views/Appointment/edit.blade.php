@extends('Template.main')
@section('title', 'Edit Appointment')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Edit Appointment #{{ $appointment->id }}</h1>
        <a href="{{ route('appointments.index') }}" class="link-back">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
        </a>
    </div>

    <div class="page-wrapper-lg">
        <form method="POST" action="{{ route('appointments.update', $appointment->id) }}" class="card-form">
            @csrf @method('PUT')

            <div>
                <label class="form-label" for="customer_id">Customer</label>
                <select name="customer_id" id="customer_id" class="form-select" required>
                    <option value="">Select customer…</option>
                    @foreach ($customers as $c)
                        <option value="{{ $c->id }}" {{ $appointment->customer_id == $c->id ? 'selected' : '' }}>
                            {{ $c->last_name }}, {{ $c->first_name }}
                        </option>
                    @endforeach
                </select>
                @error('customer_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label" for="vehicle_id">Vehicle</label>
                <select name="vehicle_id" id="vehicle_id" class="form-select" required>
                    <option value="">Select vehicle…</option>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" {{ $appointment->vehicle_id == $v->id ? 'selected' : '' }}>
                            {{ $v->year }} {{ $v->make }} {{ $v->model }} ({{ $v->license_plate ?? '—' }})
                        </option>
                    @endforeach
                </select>
                @error('vehicle_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid-2">
                <div>
                    <label class="form-label" for="appointment_date">Date</label>
                    <input type="date" name="appointment_date" id="appointment_date"
                           class="form-input" value="{{ old('appointment_date', $appointment->appointment_date) }}" required>
                    @error('appointment_date') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label" for="appointment_time">Time</label>
                    <input type="time" name="appointment_time" id="appointment_time"
                           class="form-input" value="{{ old('appointment_time', $appointment->appointment_time) }}" required>
                    @error('appointment_time') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-select" required>
                    <option value="scheduled" {{ $appointment->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="confirmed" {{ $appointment->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="in_progress" {{ $appointment->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ $appointment->status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('status') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label" for="notes">Notes</label>
                <textarea name="notes" id="notes" class="form-textarea" rows="3">{{ old('notes', $appointment->notes) }}</textarea>
                @error('notes') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn-primary">Update Appointment</button>
                <a href="{{ route('appointments.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const customerSelect = document.getElementById('customer_id');
    const vehicleSelect = document.getElementById('vehicle_id');

    customerSelect.addEventListener('change', function () {
        const customerId = this.value;
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
                    vehicleSelect.appendChild(opt);
                });
            });
    });
});
</script>
@endsection
