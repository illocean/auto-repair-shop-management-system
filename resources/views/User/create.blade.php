@extends('Template.main')
@section('title', 'Add User')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Add User</h1>
        <a href="{{ route('users.index') }}" class="btn-secondary-sm">
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
            <form method="POST" action="{{ route('users.store') }}" class="card-form">
                @csrf
                <div class="grid-2">
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
                    <input type="email" name="email" id="email" class="form-input" required>
                </div>
                <div>
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-input" required>
                </div>
                <div>
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required>
                </div>
                <div>
                    <label for="role_id" class="form-label">Role</label>
                    <select name="role_id" id="role_id" class="form-input" required>
                        <option value="">Select role</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->id }}">{{ $r->display_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary-sm">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        Create User
                    </button>
                    <a href="{{ route('users.index') }}" class="btn-secondary-sm">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
