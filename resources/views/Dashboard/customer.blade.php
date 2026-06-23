@extends('Template.main')
@section('title', 'My Dashboard')
@section('content')
<div class="page-index">

    {{-- Header --}}
    <div class="page-header">
        <div>
            <h1 class="page-title">My Dashboard</h1>
            <p class="text-xs text-slate-500 mt-1">
                <i data-lucide="calendar" class="inline w-3 h-3 align-middle"></i>
                {{ now()->format('l, F j, Y') }}
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    {{-- MY VEHICLES -- compact registry list --}}
    <div class="section-heading">
        <i data-lucide="truck"></i>
        <h2>My Vehicles</h2>
    </div>

    @if($vehicles->isEmpty())
        <div class="border border-slate-200 rounded-lg bg-white overflow-hidden mb-6">
            <div class="td-empty">
                <i data-lucide="truck" class="inline w-5 h-5 text-slate-300 mb-2 block mx-auto"></i>
                No vehicles registered.
            </div>
        </div>
    @else
        <div class="border border-slate-200 rounded-lg bg-white overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="table-workbench">
                    <thead>
                        <tr>
                            <th>Make</th>
                            <th>Model</th>
                            <th>Year</th>
                            <th>License Plate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vehicles as $v)
                            <tr>
                                <td class="td-primary"><i data-lucide="car" class="inline w-3.5 h-3.5 text-slate-400 align-middle mr-1.5"></i>{{ $v->make }}</td>
                                <td>{{ $v->model }}</td>
                                <td class="text-slate-500">{{ $v->year }}</td>
                                <td class="td-mono text-slate-500">{{ $v->license_plate ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- REPAIR ORDERS -- timeline with status dots --}}
    <div class="section-heading">
        <i data-lucide="clipboard-list"></i>
        <h2>Repair Orders</h2>
    </div>

    @if($orders->isEmpty())
        <div class="border border-slate-200 rounded-lg bg-white overflow-hidden">
            <div class="td-empty">
                <i data-lucide="clipboard-list" class="inline w-5 h-5 text-slate-300 mb-2 block mx-auto"></i>
                No repair orders yet.
            </div>
        </div>
    @else
        <div class="border border-slate-200 rounded-lg bg-white overflow-hidden">
            <div class="timeline-list px-3">
                @foreach ($orders as $o)
                    <div class="timeline-item">
                        <span class="timeline-dot
                            @if($o->status === 'completed') timeline-dot-completed
                            @elseif($o->status === 'cancelled') timeline-dot-scheduled
                            @else timeline-dot-confirmed @endif">
                            @if($o->status === 'completed')
                                <i data-lucide="check"></i>
                            @elseif($o->status === 'cancelled')
                                <i data-lucide="x"></i>
                            @elseif($o->status === 'in_progress')
                                <i data-lucide="cog"></i>
                            @else
                                <i data-lucide="clock"></i>
                            @endif
                        </span>
                        <div class="timeline-body">
                            <div class="timeline-row">
                                <span class="timeline-time">{{ $o->order_date }}</span>
                                <span class="timeline-customer">{{ $o->make }} {{ $o->model }}</span>
                                <a href="{{ route('repair-orders.show', $o->id) }}" class="toolbar-item" style="padding:0.125rem 0.375rem">
                                    <i data-lucide="eye"></i>
                                </a>
                            </div>
                            <div class="timeline-row">
                                <span class="timeline-vehicle">{{ $o->license_plate ?? '—' }}</span>
                                <span>
                                    <span class="badge-pill
                                        @if($o->status === 'completed') badge-green
                                        @elseif($o->status === 'cancelled') badge-red
                                        @elseif($o->status === 'in_progress') badge-gray
                                        @else badge-yellow @endif">
                                        <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        {{ ucfirst(str_replace('_', ' ', $o->status)) }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
