@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Events') }}</h2>
            <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Add Event') }}
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.events.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search title, city, venue...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category_id">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach (['draft', 'published', 'cancelled'] as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="upcoming">
                        <option value="">{{ __('When') }}</option>
                        <option value="1" @selected(request('upcoming') === '1')>{{ __('Upcoming') }}</option>
                        <option value="0" @selected(request('upcoming') === '0')>{{ __('Past') }}</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('City') }}</th>
                            <th>{{ __('Start') }}</th>
                            <th>{{ __('End') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($events as $event)
                            <tr>
                                <td class="fw-medium">{{ $event->title }}</td>
                                <td>{{ $event->city }}</td>
                                <td>{{ $event->start_at->format('M d, Y H:i') }}</td>
                                <td>{{ $event->end_at?->format('M d, Y H:i') ?? '-' }}</td>
                                <td>
                                    @php $statusClass = ['draft' => 'secondary', 'published' => 'success', 'cancelled' => 'danger'][$event->status] ?? 'secondary'; @endphp
                                    <span class="badge text-bg-{{ $statusClass }}">{{ ucfirst($event->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.events.show', $event) }}" class="btn btn-outline-info" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-primary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this event?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-calendar-event"></i>
                                        <p>{{ __('No events found.') }}</p>
                                        <a href="{{ route('admin.events.create') }}" class="btn btn-primary btn-sm mt-2">{{ __('Add Event') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($events->hasPages())
            <div class="card-footer bg-white">{{ $events->links() }}</div>
        @endif
    </div>
@endsection
