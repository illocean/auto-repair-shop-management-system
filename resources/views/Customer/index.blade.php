@extends('Template.main')
@section('title', 'Customers')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Customers</h1>
        <a href="{{ route('customers.create') }}" class="btn-primary-sm">+ Add Customer</a>
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
                        <th class="th-cell">#</th>
                        <th class="th-cell">Name</th>
                        <th class="th-cell">Email</th>
                        <th class="th-cell">Phone</th>
                        <th class="th-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($customers as $c)
                        <tr class="tr-hover">
                            <td class="td-dim">{{ $c->id }}</td>
                            <td class="td-primary">{{ $c->last_name }}, {{ $c->first_name }}</td>
                            <td class="td-secondary">{{ $c->email ?? '—' }}</td>
                            <td class="td-secondary">{{ $c->phone ?? '—' }}</td>
                            <td class="td-cell">
                                <div class="flex gap-3">
                                    <a href="{{ route('customers.edit', $c->id) }}" class="link-edit">Edit</a>
                                    <form action="{{ route('customers.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Delete this customer?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="link-delete">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="td-empty">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
