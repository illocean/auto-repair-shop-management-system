@extends('Template.main')
@section('title', 'Edit Customer')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Edit Customer</h1>
        <a href="{{ route('customers.index') }}" class="btn-secondary-sm">
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
            <form method="POST" action="{{ route('customers.update', $customer->id) }}" class="card-form">
                @csrf @method('PUT')
                <div class="grid-2">
                    <div>
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" name="first_name" id="first_name" value="{{ $customer->first_name }}" class="form-input" required>
                    </div>
                    <div>
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" id="last_name" value="{{ $customer->last_name }}" class="form-input" required>
                    </div>
                </div>
                <div>
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" value="{{ $customer->email }}" class="form-input">
                </div>
                <div>
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ $customer->phone }}" class="form-input">
                </div>
                <div>
                    <label for="address" class="form-label">Address</label>
                    <textarea name="address" id="address" rows="3" class="form-input">{{ $customer->address }}</textarea>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Update Customer
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn-secondary-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
