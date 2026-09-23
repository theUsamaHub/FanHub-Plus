@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Reviews') }}</h2>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col"><div class="card border-start border-warning border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Pending') }}</div><div class="fw-semibold">{{ $stats['pending'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-success border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Approved') }}</div><div class="fw-semibold">{{ $stats['approved'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-danger border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Rejected') }}</div><div class="fw-semibold">{{ $stats['rejected'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-secondary border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Total') }}</div><div class="fw-semibold">{{ $stats['total'] }}</div></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search reviews...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="status">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach (['pending', 'approved', 'rejected'] as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
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
                            <th>{{ __('User') }}</th>
                            <th>{{ __('Target') }}</th>
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Date') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reviews as $review)
                            <tr>
                                <td>{{ $review->user?->name ?? '-' }}</td>
                                <td>{{ $review->target_label }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($review->title ?: $review->body, 40) }}</td>
                                <td>
                                    @php $statusClass = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$review->status] ?? 'secondary'; @endphp
                                    <span class="badge text-bg-{{ $statusClass }}">{{ ucfirst($review->status) }}</span>
                                </td>
                                <td>{{ $review->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-outline-info" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                        @if ($review->status === 'pending')
                                            <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-success" title="{{ __('Approve') }}"><i class="bi bi-check-lg"></i></button>
                                            </form>
                                            <form action="{{ route('admin.reviews.reject', $review) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-danger" title="{{ __('Reject') }}"><i class="bi bi-x-lg"></i></button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-chat-left-text"></i>
                                        <p>{{ __('No reviews found.') }}</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($reviews->hasPages())
            <div class="card-footer bg-white">{{ $reviews->links() }}</div>
        @endif
    </div>
@endsection
