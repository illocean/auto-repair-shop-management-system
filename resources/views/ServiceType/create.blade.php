@extends('Template.main')
@section('title', 'Add Service Type')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Add Service Type</h1>
        <a href="{{ route('service-types.index') }}" class="btn-secondary-sm">
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
            <form method="POST" action="{{ route('service-types.store') }}" class="card-form">
                @csrf
                <div>
                    <label for="name" class="form-label">Service Name</label>
                    <input type="text" name="name" id="name" class="form-input" required>
                </div>
                <div>
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" rows="3" class="form-input"></textarea>
                </div>
                <div class="grid-2">
                    <div>
                        <label for="book_hours" class="form-label">Book Hours</label>
                        <input type="number" name="book_hours" id="book_hours" step="0.1" class="form-input" required>
                    </div>
                    <div>
                        <label for="rate_per_hour" class="form-label">Rate per Hour ($)</label>
                        <input type="number" name="rate_per_hour" id="rate_per_hour" step="0.01" class="form-input" required>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Create Service
                    </button>
                    <a href="{{ route('service-types.index') }}" class="btn-secondary-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
