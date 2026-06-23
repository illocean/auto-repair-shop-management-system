@extends('Template.main')
@section('title', 'Appointments')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Appointments</h1>
        <div class="flex gap-2">
            <a href="{{ route('appointments.calendar') }}" class="btn-secondary-sm">
                <i data-lucide="calendar" class="w-4 h-4"></i>
                Calendar
            </a>
            <a href="{{ route('appointments.create') }}" class="btn-primary-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Book Appointment
            </a>
        </div>
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
                        <th class="th-cell">Date</th>
                        <th class="th-cell">Time</th>
                        <th class="th-cell">Status</th>
                        @if (session('role') !== 'customer')
                            <th class="th-cell">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($appointments as $a)
                        <tr class="tr-hover">
                            <td class="td-cell">{{ $a->id }}</td>
                            <td class="td-primary">{{ $a->cust_first }} {{ $a->cust_last }}</td>
                            <td class="td-cell">{{ $a->make }} {{ $a->model }} ({{ $a->license_plate ?? '—' }})</td>
                            <td class="td-secondary">{{ $a->appointment_date }}</td>
                            <td class="td-secondary">{{ \Carbon\Carbon::parse($a->appointment_time)->format('g:i A') }}</td>
                            <td class="td-cell">
                                <span class="badge-pill
                                    @if($a->status === 'scheduled') badge-yellow
                                    @elseif($a->status === 'confirmed') badge-blue
                                    @elseif($a->status === 'in_progress') badge-gray
                                    @elseif($a->status === 'completed') badge-green
                                    @elseif($a->status === 'cancelled') badge-red
                                    @else badge-gray @endif">
                                    <svg class="status-dot w-3 h-3" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3"/>
                                    </svg>
                                    {{ ucfirst($a->status) }}
                                </span>
                            </td>
                            @if (session('role') !== 'customer')
                                <td class="td-cell">
                                    <a href="{{ route('appointments.edit', $a->id) }}" class="link-action">Edit</a>
                                    <form action="{{ route('appointments.destroy', $a->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this appointment?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="link-danger">Delete</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="7" class="td-empty">No appointments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
