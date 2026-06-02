@extends('Template.main')
@section('title', 'Edit User')
@section('content')
<div class="page-wrapper-md">
    <div class="page-header">
        <a href="{{ route('users.index') }}" class="link-back">&larr; Back to Users</a>
    </div>
    <h1 class="page-title">Edit User</h1>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', $user->id) }}" class="card-form">
        @csrf @method('PUT')
        <div class="grid-2">
            <div>
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" name="first_name" id="first_name" value="{{ $user->first_name }}" class="form-input" required>
            </div>
            <div>
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" name="last_name" id="last_name" value="{{ $user->last_name }}" class="form-input" required>
            </div>
        </div>
        <div>
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" value="{{ $user->email }}" class="form-input" required>
        </div>
        <div>
            <label for="password" class="form-label">New Password <span class="text-gray-400 font-normal">(leave blank to keep current)</span></label>
            <input type="password" name="password" id="password" class="form-input">
        </div>
        <div class="grid-2">
            <div>
                <label for="role_id" class="form-label">Role</label>
                <select name="role_id" id="role_id" class="form-input" required>
                    @foreach ($roles as $r)
                        <option value="{{ $r->id }}" {{ $userRole && $userRole->role_id == $r->id ? 'selected' : '' }}>{{ $r->display_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="is_active" class="form-label">Status</label>
                <select name="is_active" id="is_active" class="form-input">
                    <option value="1" {{ $user->is_active ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ !$user->is_active ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="btn-row">
            <button type="submit" class="btn-primary">Update User</button>
            <a href="{{ route('users.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
