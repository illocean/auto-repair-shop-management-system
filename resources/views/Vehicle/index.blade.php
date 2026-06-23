@extends('Template.main')
@section('title', 'Vehicles')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Vehicles</h1>
        <a href="{{ route('vehicles.create') }}" class="btn-primary-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Add Vehicle
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if (session('role') !== 'customer')
    <form method="GET" action="{{ route('vehicles.index') }}" class="card-filter">
        <div>
            <label for="customer_id" class="form-label">Filter by Customer</label>
            <select name="customer_id" id="customer_id" class="form-input">
                <option value="">All Customers</option>
                @foreach ($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->last_name }}, {{ $c->first_name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary-sm">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filter
        </button>
        <a href="{{ route('vehicles.index') }}" class="btn-secondary-sm">Clear</a>
    </form>
    @endif

    <div class="card-table">
        <div class="table-scroll">
            <table class="table-standard">
                <thead>
                    <tr>
                        <th class="th-cell">#</th>
                        @if (session('role') !== 'customer')
                        <th class="th-cell">Owner</th>
                        @endif
                        <th class="th-cell">Vehicle</th>
                        <th class="th-cell">Year</th>
                        <th class="th-cell">Plate</th>
                        <th class="th-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($vehicles as $v)
                        <tr class="tr-hover">
                            <td class="td-cell">{{ $v->id }}</td>
                            @if (session('role') !== 'customer')
                            <td class="td-primary">{{ $v->cust_first }} {{ $v->cust_last }}</td>
                            @endif
                            <td class="td-cell">{{ $v->make }} {{ $v->model }}</td>
                            <td class="td-secondary">{{ $v->year ?? '—' }}</td>
                            <td class="td-secondary">{{ $v->license_plate ?? '—' }}</td>
                            <td class="td-cell">
                                @if (session('role') === 'customer')
                                    <span class="text-xs text-gray-400">—</span>
                                @else
                                    <a href="{{ route('vehicles.edit', $v->id) }}" class="link-action">Edit</a>
                                    <form action="{{ route('vehicles.destroy', $v->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this vehicle?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="link-danger">Delete</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ session('role') === 'customer' ? 5 : 6 }}" class="td-empty">No vehicles found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
