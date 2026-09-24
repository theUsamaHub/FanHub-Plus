@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Review Detail') }}</h2>
            <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card fh-adm-detail-card">
                <div class="card-body">
                    <table class="table mb-0 fh-adm-detail-table">
                        <tbody>
                            <tr><td class="fw-semibold" style="width:180px;">{{ __('User') }}</td><td>{{ $review->user?->name ?? '-' }} @if ($review->user) &lt;{{ $review->user->email }}&gt; @endif</td></tr>
                            <tr><td class="fw-semibold">{{ __('Target') }}</td><td>{{ $review->target_label }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Title') }}</td><td>{{ $review->title ?: '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Body') }}</td><td style="white-space: pre-wrap;">{{ $review->body }}</td></tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Status') }}</td>
                                <td>
                                    @php $statusTone = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$review->status] ?? 'muted'; @endphp
                                    <span class="fh-adm-chip" data-tone="{{ $statusTone }}">{{ ucfirst($review->status) }}</span>
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">{{ __('Date') }}</td><td>{{ $review->created_at->format('M d, Y H:i') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Moderation') }}</h6></div>
                <div class="card-body d-grid gap-2">
                    @if ($review->status !== 'approved')
                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100">{{ __('Approve') }}</button>
                        </form>
                    @endif
                    @if ($review->status !== 'rejected')
                        <form action="{{ route('admin.reviews.reject', $review) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger w-100">{{ __('Reject') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card border-danger fh-adm-danger-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold text-danger">{{ __('Danger Zone') }}</h6></div>
                <div class="card-body">
                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('{{ __('Delete this review?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>{{ __('Delete Review') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
