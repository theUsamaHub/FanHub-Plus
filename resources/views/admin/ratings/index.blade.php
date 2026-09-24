@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Ratings') }}</h2>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col"><div class="card border-start border-primary border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Total') }}</div><div class="fw-semibold">{{ $stats['total'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-info border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Star ratings') }}</div><div class="fw-semibold">{{ $stats['star'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-secondary border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Star average') }}</div><div class="fw-semibold">{{ $starAverage ? number_format((float) $starAverage, 2) : '-' }}</div></div></div></div>
        <div class="col"><div class="card border-start border-success border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Thumbs up') }}</div><div class="fw-semibold">{{ $stats['thumbs_up'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-danger border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Thumbs down') }}</div><div class="fw-semibold">{{ $stats['thumbs_down'] }}</div></div></div></div>
    </div>

    <div class="card mb-4 fh-adm-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.ratings.index') }}" class="row g-2">
                <div class="col-md-3">
                    <select class="form-select" name="rating_type">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="star" @selected(request('rating_type') === 'star')>{{ __('Star') }}</option>
                        <option value="thumbs" @selected(request('rating_type') === 'thumbs')>{{ __('Thumbs') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.ratings.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card fh-adm-form-card">
        <div class="card-body p-0">
            <div class="table-responsive fh-adm-table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Target') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Value') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ratings as $rating)
                            <tr>
                                <td>{{ $rating->user?->name ?? '-' }}</td>
                                <td>{{ $rating->target_label }}</td>
                                <td>{{ $rating->rating_type === 'star' ? __('Star') : __('Thumbs') }}</td>
                                <td>
                                    @if ($rating->rating_type === 'star')
                                        {{ $rating->stars !== null ? $rating->stars.' / 5' : '-' }}
                                    @else
                                        {{ $rating->is_thumbs_up === true ? __('Thumbs up') : ($rating->is_thumbs_up === false ? __('Thumbs down') : '-') }}
                                    @endif
                                </td>
                                <td>{{ $rating->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <form action="{{ route('admin.ratings.destroy', $rating) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this rating?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="fh-adm-empty">
                                        <i class="bi bi-star"></i>
                                        <p>{{ __('No ratings found.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($ratings->hasPages())
            <div class="card-footer bg-white">{{ $ratings->links() }}</div>
        @endif
    </div>
@endsection
