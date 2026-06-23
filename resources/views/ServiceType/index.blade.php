@extends('Template.main')
@section('title', 'Service Types')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Service Types</h1>
        <a href="{{ route('service-types.create') }}" class="btn-primary-sm">New Service</a>
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
                        <th class="th-cell">Service</th>
                        <th class="th-cell">Book Hours</th>
                        <th class="th-cell">Rate/Hour</th>
                        <th class="th-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($serviceTypes as $s)
                        <tr class="tr-hover">
                            <td class="td-cell">{{ $s->id }}</td>
                            <td class="td-primary">{{ $s->name }}</td>
                            <td class="td-secondary">{{ $s->book_hours }}h</td>
                            <td class="td-secondary">${{ number_format($s->rate_per_hour, 2) }}</td>
                            <td class="td-cell">
                                <a href="{{ route('service-types.edit', $s->id) }}" class="link-action">Edit</a>
                                <form action="{{ route('service-types.destroy', $s->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this service type?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="link-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="td-empty">No service types found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
