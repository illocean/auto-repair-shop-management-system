@extends('Template.main')
@section('title', 'Appointment Calendar')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Appointment Calendar</h1>
        <div class="flex gap-2">
            <a href="{{ route('appointments.index') }}" class="btn-secondary-sm">
                <i data-lucide="list" class="w-4 h-4"></i>
                List View
            </a>
            <a href="{{ route('appointments.create') }}" class="btn-primary-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Book Appointment
            </a>
        </div>
    </div>

    {{-- Month navigation --}}
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('appointments.calendar', ['month' => $carbon->copy()->subMonth()->month, 'year' => $carbon->copy()->subMonth()->year]) }}"
           class="btn-secondary-sm">&larr; {{ $carbon->copy()->subMonth()->format('M') }}</a>
        <h2 class="text-lg font-semibold text-slate-800">{{ $carbon->format('F Y') }}</h2>
        <a href="{{ route('appointments.calendar', ['month' => $carbon->copy()->addMonth()->month, 'year' => $carbon->copy()->addMonth()->year]) }}"
           class="btn-secondary-sm">{{ $carbon->copy()->addMonth()->format('M') }} &rarr;</a>
    </div>

    {{-- Calendar grid --}}
    <div class="card">
        <div class="grid grid-cols-7 gap-px bg-slate-200">
            @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                <div class="bg-slate-50 px-2 py-1.5 text-center text-xs font-semibold text-slate-500 uppercase">{{ $day }}</div>
            @endforeach

            {{-- Empty cells before first day --}}
            @for ($i = 0; $i < $carbon->copy()->startOfMonth()->dayOfWeek; $i++)
                <div class="bg-white p-2 min-h-[100px]"></div>
            @endfor

            {{-- Day cells --}}
            @for ($d = 1; $d <= $carbon->daysInMonth; $d++)
                @php
                    $date = sprintf('%s-%s-%s', $carbon->year, str_pad($carbon->month, 2, '0', STR_PAD_LEFT), str_pad($d, 2, '0', STR_PAD_LEFT));
                    $dayAppointments = $grouped->get($date, collect());
                    $isToday = $date === now()->format('Y-m-d');
                @endphp
                <div class="bg-white p-1.5 min-h-[100px] {{ $isToday ? 'ring-2 ring-indigo-400 ring-inset' : '' }}">
                    <div class="text-xs font-semibold {{ $isToday ? 'text-indigo-600' : 'text-slate-600' }} mb-1">{{ $d }}</div>
                    @foreach ($dayAppointments as $apt)
                        <div class="text-[10px] px-1 py-0.5 rounded mb-0.5 truncate cursor-pointer
                            @if($apt->status === 'scheduled') bg-yellow-100 text-yellow-800
                            @elseif($apt->status === 'confirmed') bg-blue-100 text-blue-800
                            @elseif($apt->status === 'completed') bg-green-100 text-green-800
                            @elseif($apt->status === 'cancelled') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800 @endif"
                            title="{{ $apt->cust_first }} {{ $apt->cust_last }} - {{ \Carbon\Carbon::parse($apt->appointment_time)->format('g:i A') }}">
                            {{ \Carbon\Carbon::parse($apt->appointment_time)->format('g:i A') }}
                            {{ $apt->cust_first }}
                        </div>
                    @endforeach
                </div>
            @endfor

            {{-- Empty cells after last day --}}
            @php $remaining = 7 - (($carbon->copy()->startOfMonth()->dayOfWeek + $carbon->daysInMonth) % 7); @endphp
            @if ($remaining < 7)
                @for ($i = 0; $i < $remaining; $i++)
                    <div class="bg-white p-2 min-h-[100px]"></div>
                @endfor
            @endif
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-3 mt-4 text-xs text-slate-600">
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span> Scheduled</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-400"></span> Confirmed</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-green-400"></span> Completed</span>
        <span class="inline-flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span> Cancelled</span>
    </div>
</div>
@endsection
