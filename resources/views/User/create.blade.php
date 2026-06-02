@extends('Template.main')
@section('title', 'New User')
@section('content')
<div class="page-wrapper-md">
    <div class="page-header">
        <a href="{{ route('users.index') }}" class="link-back">&larr; Back to Users</a>
    </div>
    <h1 class="page-title">New User</h1>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

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
        <div class="grid-2">
            <div>
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-input" required>
            </div>
            <div>
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" required>
            </div>
        </div>
        <div class="grid-2">
            <div>
                <label for="role_id" class="form-label">Role</label>
                <select name="role_id" id="role_id" class="form-input" required>
                    @foreach ($roles as $r)
                        <option value="{{ $r->id }}">{{ $r->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="is_active" class="form-label">Status</label>
                <select name="is_active" id="is_active" class="form-input">
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
        </div>
        <div class="btn-row">
            <button type="submit" class="btn-primary">Create User</button>
            <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
