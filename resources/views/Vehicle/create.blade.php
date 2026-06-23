@extends('Template.main')
@section('title', 'Add Vehicle')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Add Vehicle</h1>
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
                <div class="grid-2">
                    <div>
                        <label for="make" class="form-label">Make</label>
                        <input type="text" name="make" id="make" class="form-input" required>
                    </div>
                    <div>
                        <label for="model" class="form-label">Model</label>
                        <input type="text" name="model" id="model" class="form-input" required>
                    </div>
                </div>
                <div class="grid-2">
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
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Save Vehicle
                    </button>
                    <a href="{{ route('vehicles.index') }}" class="btn-secondary-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
