@extends('Template.main')
@section('title', 'Edit Vehicle')
@section('content')
<div class="page-wrapper-md">
    <div class="mb-6">
        <a href="{{ route('vehicles.index') }}" class="link-back">&larr; Back to Vehicles</a>
    </div>
    <h1 class="page-title">Edit Vehicle</h1>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

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
        <div class="grid-2-responsive">
            <div>
                <label for="make" class="form-label">Make</label>
                <input type="text" name="make" id="make" value="{{ $vehicle->make }}" class="form-input" required>
            </div>
            <div>
                <label for="model" class="form-label">Model</label>
                <input type="text" name="model" id="model" value="{{ $vehicle->model }}" class="form-input" required>
            </div>
        </div>
        <div class="grid-2-responsive">
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
        <div class="btn-row">
            <button type="submit" class="btn-primary">Update Vehicle</button>
            <a href="{{ route('vehicles.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
