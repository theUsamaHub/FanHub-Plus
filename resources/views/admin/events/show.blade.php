@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ $event->title }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>{{ __('Edit') }}</a>
                <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr>
                                <td class="fw-semibold" style="width:180px;">{{ __('Cover') }}</td>
                                <td>
                                    @if ($event->coverMedia)
                                        <img src="{{ $event->coverMedia->url }}" alt="{{ $event->title }}" class="img-thumbnail" style="max-height: 140px;">
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">{{ __('Title') }}</td><td>{{ $event->title }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Description') }}</td><td style="white-space: pre-wrap;">{{ $event->description ?: '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Category') }}</td><td>{{ $event->category?->name ?? '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('City') }}</td><td>{{ $event->city }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Venue') }}</td><td>{{ $event->venue ?: '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Address') }}</td><td>{{ $event->address ?: '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Coordinates') }}</td><td>{{ $event->latitude && $event->longitude ? $event->latitude.', '.$event->longitude : '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Start') }}</td><td>{{ $event->start_at->format('M d, Y H:i') }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('End') }}</td><td>{{ $event->end_at?->format('M d, Y H:i') ?? '-' }}</td></tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Status') }}</td>
                                <td>
                                    @php $statusClass = ['draft' => 'secondary', 'published' => 'success', 'cancelled' => 'danger'][$event->status] ?? 'secondary'; @endphp
                                    <span class="badge text-bg-{{ $statusClass }}">{{ ucfirst($event->status) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Ticket URL') }}</td>
                                <td>
                                    @if ($event->ticket_url)
                                        <a href="{{ $event->ticket_url }}" target="_blank" rel="noopener">{{ $event->ticket_url }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-danger">
                <div class="card-header"><h6 class="mb-0 fw-semibold text-danger">{{ __('Danger Zone') }}</h6></div>
                <div class="card-body">
                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('{{ __('Delete this event?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>{{ __('Delete Event') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
