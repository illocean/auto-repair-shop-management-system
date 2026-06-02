@extends('Template.main')
@section('title', 'Add Customer')
@section('content')
<div class="page-wrapper-md">
    <div>
        <a href="{{ route('customers.index') }}" class="link-back">&larr; Back to Customers</a>
    </div>
    <h1 class="page-title">Add Customer</h1>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('customers.store') }}" class="card-form">
        @csrf
        <div class="grid-2-responsive">
            <div>
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" id="first_name" class="form-input" required>
            </div>
            <div>
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" id="last_name" class="form-input" required>
            </div>
        </div>
        <div>
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-input">
        </div>
        <div>
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" id="phone" class="form-input">
        </div>
        <div>
            <label for="address" class="form-label">Address</label>
            <textarea name="address" id="address" rows="3" class="form-input"></textarea>
        </div>
        <div class="btn-row">
            <button type="submit" class="btn-primary">Save Customer</button>
            <a href="{{ route('customers.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
