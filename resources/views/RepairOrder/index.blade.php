@extends('Template.main')
@section('title', 'Repair Orders')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Repair Orders</h1>
        <a href="{{ route('repair-orders.create') }}" class="btn-primary-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            New Order
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
                        <th class="th-cell">Customer</th>
                        <th class="th-cell">Vehicle</th>
                        <th class="th-cell">Advisor</th>
                        <th class="th-cell">Date</th>
                        <th class="th-cell">Status</th>
                        <th class="th-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($orders as $o)
                        <tr class="tr-hover">
                            <td class="td-cell">{{ $o->id }}</td>
                            <td class="td-primary">{{ $o->cust_first }} {{ $o->cust_last }}</td>
                            <td class="td-cell">{{ $o->year }} {{ $o->make }} {{ $o->model }}</td>
                            <td class="td-secondary">{{ $o->service_advisor_name }}</td>
                            <td class="td-secondary">{{ $o->order_date }}</td>
                            <td class="td-cell">
                                <span class="badge-pill
                                    @if ($o->status === 'open') badge-yellow
                                    @elseif($o->status === 'in_progress') badge-gray
                                    @elseif($o->status === 'completed') badge-green
                                    @elseif($o->status === 'cancelled') badge-red
                                    @else badge-gray @endif">
                                    <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    {{ str_replace('_', ' ', ucfirst($o->status)) }}
                                </span>
                            </td>
                            <td class="td-cell">
                                <a href="{{ route('repair-orders.show', $o->id) }}" class="link-action">View</a>
                                @if (session('role') !== 'customer')
                                <a href="{{ route('repair-orders.edit', $o->id) }}" class="link-action">Edit</a>
                                <form action="{{ route('repair-orders.destroy', $o->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this order?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="link-danger">Delete</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="td-empty">No repair orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
