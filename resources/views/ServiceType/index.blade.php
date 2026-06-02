@extends('Template.main')
@section('title', 'Service Types')
@section('content')
<div class="page-index">
    <div class="page-header">
        <h1 class="page-title">Service Types</h1>
        <a href="{{ route('service-types.create') }}" class="btn-primary-sm">+ New Service</a>
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
                        <th class="th-cell">Name</th>
                        <th class="th-cell">Description</th>
                        <th class="th-cell">Book Hours</th>
                        <th class="th-cell">Rate/Hour</th>
                        <th class="th-cell">Actions</th>
                    </tr>
                </thead>
                <tbody class="tbody-divide">
                    @forelse ($serviceTypes as $st)
                        <tr class="tr-hover">
                            <td class="td-primary">{{ $st->name }}</td>
                            <td class="td-secondary max-w-xs truncate">{{ $st->description }}</td>
                            <td class="td-secondary">{{ $st->book_hours }}</td>
                            <td class="td-secondary">${{ number_format($st->rate_per_hour, 2) }}</td>
                            <td class="td-cell">
                                <div class="flex gap-3">
                                    <a href="{{ route('service-types.edit', $st->id) }}" class="link-edit">Edit</a>
                                    <form action="{{ route('service-types.destroy', $st->id) }}" method="POST" onsubmit="return confirm('Delete this service type?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="link-delete">Delete</button>
                                    </form>
                                </div>
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
