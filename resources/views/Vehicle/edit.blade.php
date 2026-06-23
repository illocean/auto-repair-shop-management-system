@extends('Template.main')
@section('title', 'Edit Vehicle')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Edit Vehicle</h1>
        <a href="{{ route('vehicles.index') }}" class="btn-secondary-sm">
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
            <form method="POST" action="{{ route('vehicles.update', $vehicle->id) }}" class="card-form">
                @csrf @method('PUT')
                <div>
                    <label for="customer_id" class="form-label">Owner</label>
                    <select name="customer_id" id="customer_id" class="form-input" required>
                        @foreach ($customers as $c)
                            <option value="{{ $c->id }}" {{ $vehicle->customer_id == $c->id ? 'selected' : '' }}>{{ $c->last_name }}, {{ $c->first_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid-2">
                    <div>
                        <label for="make" class="form-label">Make</label>
                        <input type="text" name="make" id="make" value="{{ $vehicle->make }}" class="form-input" required>
                    </div>
                    <div>
                        <label for="model" class="form-label">Model</label>
                        <input type="text" name="model" id="model" value="{{ $vehicle->model }}" class="form-input" required>
                    </div>
                </div>
                <div class="grid-2">
                    <div>
                        <label for="year" class="form-label">Year</label>
                        <input type="number" name="year" id="year" min="1901" max="2155" value="{{ $vehicle->year }}" class="form-input">
                    </div>
                    <div>
                        <label for="license_plate" class="form-label">License Plate</label>
                        <input type="text" name="license_plate" id="license_plate" value="{{ $vehicle->license_plate }}" class="form-input">
                    </div>
                </div>
                <div>
                    <label for="vin" class="form-label">VIN</label>
                    <input type="text" name="vin" id="vin" maxlength="17" value="{{ $vehicle->vin }}" class="form-input">
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Update Vehicle
                    </button>
                    <a href="{{ route('vehicles.index') }}" class="btn-secondary-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
