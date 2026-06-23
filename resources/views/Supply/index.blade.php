@extends('Template.main')
@section('title', 'Supplies')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Supplies Inventory</h1>
        @if (in_array(session('role'), ['admin', 'manager']))
            <a href="{{ route('supplies.create') }}" class="btn-primary-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Supply
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- Low stock alerts --}}
    @php $lowStock = $supplies->filter(fn ($s) => $s->quantity <= $s->low_stock_threshold); @endphp
    @if ($lowStock->count() > 0)
        <div class="alert-error">
            <strong>Low stock alert:</strong>
            @foreach ($lowStock as $s)
                <p>{{ $s->name }} — only {{ $s->quantity }} {{ $s->unit }}(s) left (threshold: {{ $s->low_stock_threshold }})</p>
            @endforeach
        </div>
    @endif

    <div class="card-table">
        <div class="table-scroll">
            <table class="table-standard">
                <thead>
                    <tr>
                        <th class="th-cell">#</th>
                        <th class="th-cell">Name</th>
                        <th class="th-cell">Quantity</th>
                        <th class="th-cell">Unit</th>
                        <th class="th-cell">Unit Price</th>
                        <th class="th-cell">Total Value</th>
                        <th class="th-cell">Status</th>
                        @if (in_array(session('role'), ['admin', 'manager']))
                            <th class="th-cell">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($supplies as $s)
                        @php $isLow = $s->quantity <= $s->low_stock_threshold; @endphp
                        <tr class="{{ $isLow ? 'tr-row-red' : 'tr-hover' }}">
                            <td class="td-cell">{{ $s->id }}</td>
                            <td class="td-primary">{{ $s->name }}</td>
                            <td class="td-cell">{{ $s->quantity }}</td>
                            <td class="td-secondary">{{ $s->unit }}</td>
                            <td class="td-secondary">${{ number_format($s->unit_price, 2) }}</td>
                            <td class="td-secondary">${{ number_format($s->quantity * $s->unit_price, 2) }}</td>
                            <td class="td-cell">
                                @if ($isLow)
                                    <span class="badge-pill badge-red">
                                        <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Low Stock
                                    </span>
                                @else
                                    <span class="badge-pill badge-green">
                                        <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        In Stock
                                    </span>
                                @endif
                            </td>
                            @if (in_array(session('role'), ['admin', 'manager']))
                                <td class="td-cell">
                                    <a href="{{ route('supplies.edit', $s->id) }}" class="link-action">Edit</a>
                                    <form action="{{ route('supplies.destroy', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this supply?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="link-danger">Delete</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="8" class="td-empty">No supplies in inventory.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
