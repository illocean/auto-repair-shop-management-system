@extends('Template.main')

@section('content')
<div class="container-max py-8">
    <h1 class="page-title">My Dashboard</h1>

    @if(session('success'))
        <div class="alert-success mb-6">{{ session('success') }}</div>
    @endif

    {{-- Vehicles --}}
    <div class="card mb-8">
        <div class="card-header">
            <h2 class="card-title">My Vehicles</h2>
        </div>
        <div class="card-body p-0">
            @if($vehicles->isEmpty())
                <p class="text-gray-500 text-sm p-6">No vehicles registered.</p>
            @else
                <table class="table-base">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="th-cell">Make</th>
                            <th class="th-cell">Model</th>
                            <th class="th-cell">Year</th>
                            <th class="th-cell">License Plate</th>
                        </tr>
                    </thead>
                    <tbody class="tbody-divide">
                        @foreach ($vehicles as $v)
                            <tr class="tr-hover">
                                <td class="td-primary">{{ $v->make }}</td>
                                <td class="td-primary">{{ $v->model }}</td>
                                <td class="td-secondary">{{ $v->year }}</td>
                                <td class="td-secondary">{{ $v->license_plate ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Repair Orders --}}
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Repair Orders</h2>
        </div>
        <div class="card-body p-0">
            @if($orders->isEmpty())
                <p class="text-gray-500 text-sm p-6">No repair orders yet.</p>
            @else
                <table class="table-base">
                    <thead>
                        <tr class="bg-gray-50 text-left">
                            <th class="th-cell">Date</th>
                            <th class="th-cell">Vehicle</th>
                            <th class="th-cell">Status</th>
                            <th class="th-cell">Services</th>
                        </tr>
                    </thead>
                    <tbody class="tbody-divide">
                        @foreach ($orders as $o)
                            @php
                                $sColors = [
                                    'open'        => ['row' => 'tr-row-yellow', 'border' => 'td-border-yellow'],
                                    'in_progress' => ['row' => 'tr-row-gray',   'border' => 'td-border-gray'],
                                    'completed'   => ['row' => 'tr-row-green',  'border' => 'td-border-green'],
                                    'cancelled'   => ['row' => 'tr-row-red',    'border' => 'td-border-red'],
                                ];
                                $sc = $sColors[$o->status] ?? ['row' => '', 'border' => ''];
                            @endphp
                            <tr class="tr-hover {{ $sc['row'] }}">
                                <td class="td-secondary whitespace-nowrap {{ $sc['border'] }}">{{ $o->order_date }}</td>
                                <td class="td-primary">{{ $o->make }} {{ $o->model }} ({{ $o->license_plate ?? '—' }})</td>
                                <td class="td-cell">
                                    <span class="badge-pill
                                        @if($o->status === 'completed') badge-green
                                        @elseif($o->status === 'cancelled') badge-red
                                        @else badge-gray @endif">
                                        {{ ucfirst(str_replace('_', ' ', $o->status)) }}
                                    </span>
                                </td>
                                <td class="td-cell">
                                    <a href="{{ route('repair-orders.show', $o->id) }}" class="link">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
