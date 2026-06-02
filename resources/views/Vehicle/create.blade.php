@extends('Template.main')
@section('title', 'Add Vehicle')
@section('content')
<div class="page-wrapper-md">
    <div class="mb-6">
        <a href="{{ route('vehicles.index') }}" class="link-back">&larr; Back to Vehicles</a>
    </div>
    <h1 class="page-title">Add Vehicle</h1>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('vehicles.store') }}" class="card-form">
        @csrf
        @if (session('role') !== 'customer')
        <div>
            <label for="customer_id" class="form-label">Owner</label>
            <select name="customer_id" id="customer_id" class="form-input" required>
                <option value="">Select customer</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->last_name }}, {{ $c->first_name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="grid-2-responsive">
            <div>
                <label for="make" class="form-label">Make</label>
                <input type="text" name="make" id="make" class="form-input" required>
            </div>
            <div>
                <label for="model" class="form-label">Model</label>
                <input type="text" name="model" id="model" class="form-input" required>
            </div>
        </div>
        <div class="grid-2-responsive">
            <div>
                <label for="year" class="form-label">Year</label>
                <input type="number" name="year" id="year" min="1901" max="2155" class="form-input">
            </div>
            <div>
                <label for="license_plate" class="form-label">License Plate</label>
                <input type="text" name="license_plate" id="license_plate" class="form-input">
            </div>
        </div>
        <div>
            <label for="vin" class="form-label">VIN</label>
            <input type="text" name="vin" id="vin" maxlength="17" class="form-input">
        </div>
        <div class="btn-row">
            <button type="submit" class="btn-primary">Save Vehicle</button>
            <a href="{{ route('vehicles.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
