@extends('Template.main')
@section('title', 'Repair Orders')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Repair Orders</h1>
        <a href="{{ route('repair-orders.create') }}" class="btn-primary-sm">+ New Order</a>
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
                        @php
                            $statusColors = [
                                'open'        => ['row' => 'tr-row-yellow', 'border' => 'td-border-yellow'],
                                'in_progress' => ['row' => 'tr-row-gray',   'border' => 'td-border-gray'],
                                'completed'   => ['row' => 'tr-row-green',  'border' => 'td-border-green'],
                            ];
                            $sColor = $statusColors[$o->status] ?? ['row' => '', 'border' => ''];
                        @endphp
                        <tr class="tr-hover {{ $sColor['row'] }}">
                            <td class="td-dim {{ $sColor['border'] }}">{{ $o->id }}</td>
                            <td class="td-primary">{{ $o->cust_first }} {{ $o->cust_last }}</td>
                            <td class="td-cell">{{ $o->year }} {{ $o->make }} {{ $o->model }}</td>
                            <td class="td-secondary">{{ $o->service_advisor_name }}</td>
                            <td class="td-secondary">{{ $o->order_date }}</td>
                            <td class="td-cell">
                                <span class="badge-pill
                                    @if ($o->status === 'open') badge-yellow
                                    @elseif($o->status === 'in_progress') badge-gray
                                    @elseif($o->status === 'completed') badge-green
                                    @else badge-gray @endif">
                                    {{ str_replace('_', ' ', ucfirst($o->status)) }}
                                </span>
                            </td>
                            <td class="td-cell">
                                <div class="flex gap-3">
                                    <a href="{{ route('repair-orders.show', $o->id) }}" class="text-green-600 hover:text-green-800 text-sm font-medium">View</a>
                                    @if (session('role') !== 'customer')
                                    <a href="{{ route('repair-orders.edit', $o->id) }}" class="link-edit">Edit</a>
                                    <form action="{{ route('repair-orders.destroy', $o->id) }}" method="POST" onsubmit="return confirm('Delete this order?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="link-delete">Delete</button>
                                    </form>
                                    @endif
                                </div>
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
