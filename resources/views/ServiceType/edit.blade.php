@extends('Template.main')
@section('title', 'Edit Service Type')
@section('content')
<div class="page-wrapper-md">
    <div class="mb-6">
        <a href="{{ route('service-types.index') }}" class="link-back">&larr; Back to Services</a>
    </div>
    <h1 class="page-title">Edit Service Type</h1>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('service-types.update', $serviceType->id) }}" class="card-form">
        @csrf @method('PUT')
        <div>
            <label for="name" class="form-label">Service Name</label>
            <input type="text" name="name" id="name" value="{{ $serviceType->name }}" class="form-input" required>
        </div>
        <div>
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" rows="3" class="form-input">{{ $serviceType->description }}</textarea>
        </div>
        <div class="grid-2-responsive">
            <div>
                <label for="book_hours" class="form-label">Book Hours</label>
                <input type="number" step="0.25" name="book_hours" id="book_hours" value="{{ $serviceType->book_hours }}" class="form-input" required>
            </div>
            <div>
                <label for="rate_per_hour" class="form-label">Rate per Hour</label>
                <input type="number" step="0.01" name="rate_per_hour" id="rate_per_hour" value="{{ $serviceType->rate_per_hour }}" class="form-input" required>
            </div>
        </div>
        <div class="btn-row">
            <button type="submit" class="btn-primary">Update Service</button>
            <a href="{{ route('service-types.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
