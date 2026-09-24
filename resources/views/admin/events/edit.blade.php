@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Edit Event') }}</h2>
            <a href="{{ route('admin.events.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    @include('admin.events.partials.form', [
        'action' => route('admin.events.update', $event),
        'method' => 'PUT',
        'event' => $event,
    ])
@endsection
