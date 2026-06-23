@extends('Template.main')
@section('title', 'Users')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Users</h1>
        <a href="{{ route('users.create') }}" class="btn-primary-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            New User
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-table">
        <div class="table-scroll">
            <table class="table-standard">
                <thead>
                    <tr>
                        <th class="th-cell">#</th>
                        <th class="th-cell">Name</th>
                        <th class="th-cell">Email</th>
                        <th class="th-cell">Role</th>
                        <th class="th-cell">Active</th>
                        <th class="th-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($users as $u)
                        <tr class="tr-hover">
                            <td class="td-cell">{{ $u->id }}</td>
                            <td class="td-primary">{{ $u->last_name }}, {{ $u->first_name }}</td>
                            <td class="td-secondary">{{ $u->email }}</td>
                            <td class="td-cell">
                                <span class="badge-pill
                                    @if($u->role === 'admin') badge-purple
                                    @elseif($u->role === 'manager') badge-blue
                                    @elseif($u->role === 'staff') badge-gray
                                    @else badge-gray @endif">
                                    {{ $u->role_display ?? $u->role }}
                                </span>
                            </td>
                            <td class="td-cell">
                                @if ($u->is_active)
                                    <span class="badge-pill badge-green">
                                        <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Active
                                    </span>
                                @else
                                    <span class="badge-pill badge-red">
                                        <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="td-cell">
                                <a href="{{ route('users.edit', $u->id) }}" class="link-action">Edit</a>
                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this user?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="link-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="td-empty">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
