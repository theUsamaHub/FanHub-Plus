@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Edit Merchandise') }}</h2>
            <a href="{{ route('admin.merchandise.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    @include('admin.merchandise.partials.form', [
        'action' => route('admin.merchandise.update', $item),
        'method' => 'PUT',
        'item' => $item,
    ])
@endsection
