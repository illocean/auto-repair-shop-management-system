@extends('Template.main')
@section('title', 'Users')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Users</h1>
        <a href="{{ route('users.create') }}" class="btn-primary-sm">+ New User</a>
    </div>

    @if ($errors->any())
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="card-table">
        <div class="table-scroll">
            <table class="table-standard">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="th-cell">Name</th>
                        <th class="th-cell">Email</th>
                        <th class="th-cell">Role</th>
                        <th class="th-cell">Active</th>
                        <th class="th-cell">Last Login</th>
                        <th class="th-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($users as $u)
                        @php
                            $roleColors = [
                                'admin'   => ['row' => 'tr-row-purple', 'border' => 'td-border-purple'],
                                'manager' => ['row' => 'tr-row-blue',   'border' => 'td-border-blue'],
                                'staff'   => ['row' => 'tr-row-green',  'border' => 'td-border-green'],
                            ];
                            $rColor = $roleColors[$u->role_name ?? ''] ?? ['row' => '', 'border' => ''];
                        @endphp
                        <tr class="tr-hover {{ $rColor['row'] }}">
                            <td class="td-primary {{ $rColor['border'] }}">{{ $u->first_name }} {{ $u->last_name }}</td>
                            <td class="td-secondary">{{ $u->email }}</td>
                            <td class="td-cell">
                                <span class="badge-pill
                                    @if (($u->role_name ?? '') === 'admin') badge-purple
                                    @elseif(($u->role_name ?? '') === 'manager') badge-gray
                                    @else badge-gray @endif">
                                    {{ ucfirst($u->role_name ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="td-cell">
                                <span class="badge-pill {{ $u->is_active ? 'badge-green' : 'badge-red' }}">
                                    {{ $u->is_active ? 'Yes' : 'No' }}
                                </span>
                            </td>
                            <td class="td-secondary">{{ $u->last_login ?? 'Never' }}</td>
                            <td class="td-cell">
                                <div class="flex gap-3">
                                    <a href="{{ route('users.edit', $u->id) }}" class="link-edit">Edit</a>
                                    <form action="{{ route('users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="link-delete">Delete</button>
                                    </form>
                                </div>
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
