@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Admin Dashboard') }}</h2>
                <p class="text-muted mb-0" style="font-size: 0.875rem;">{{ now()->format('l, M j, Y') }}</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-clockwise me-1"></i>{{ __('Refresh') }}
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        @foreach ($stats as $stat)
            <div class="col-md-4 col-xl-3">
                <a href="{{ route($stat['route'], $stat['params'] ?? []) }}" class="fh-adm-kpi" data-accent="{{ $stat['color'] }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div class="min-w-0">
                                <span class="fh-adm-kpi-label">{{ $stat['label'] }}</span>
                                <div class="fh-adm-kpi-value">{{ $stat['count'] }}</div>
                            </div>
                            <div class="fh-adm-kpi-icon flex-shrink-0">
                                <i class="bi {{ $stat['icon'] }}"></i>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Quick actions -->
    <div class="card mb-4">
        <div class="card-body d-flex flex-wrap gap-2">
            <a href="{{ route('admin.contents.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>{{ __('Add Content') }}</a>
            <a href="{{ route('admin.media.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-upload me-1"></i>{{ __('Upload Media') }}</a>
            <a href="{{ route('admin.events.create') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar-plus me-1"></i>{{ __('Add Event') }}</a>
            <a href="{{ route('admin.characters.create') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-person-plus me-1"></i>{{ __('Add Character') }}</a>
            <a href="{{ route('admin.merchandise.create') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-seam me-1"></i>{{ __('Add Merchandise') }}</a>
            <a href="{{ route('admin.submissions.index') }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-inbox me-1"></i>{{ __('Review Submissions') }}</a>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('User growth (last 30 days)') }}</h6></div>
                <div class="card-body">
                    @php $maxVal = max(1, collect($chartData)->max('users')); @endphp
                    <div class="d-flex align-items-end gap-1" style="height: 180px;">
                        @foreach ($chartData as $day)
                            <div class="flex-grow-1 d-flex flex-column align-items-center justify-content-end" style="height: 100%;">
                                <div class="rounded-top" style="width:100%; height: {{ ($day['users'] / $maxVal) * 160 }}px; background: var(--bs-primary); min-height: 2px;" title="{{ $day['users'] }} users"></div>
                                <small class="text-muted mt-1" style="font-size:0.6rem;">{{ $day['label'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Content by status') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <tbody>
                            @foreach (['draft', 'pending_review', 'published', 'rejected'] as $status)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $status)) }}</td>
                                    <td class="text-end fw-semibold">{{ $contentByStatus[$status] ?? 0 }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Recent user submissions') }}</h6>
                    <a href="{{ route('admin.submissions.index') }}" class="btn btn-sm btn-link text-decoration-none">{{ __('View all') }}</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Submitter') }}</th>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSubmissions as $item)
                                <tr>
                                    <td>{{ $item->submittedBy?->name ?? __('Guest') }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($item->title, 30) }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.submissions.show', $item) }}" class="btn btn-outline-info btn-sm">{{ __('Review') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">{{ __('All caught up - no fan submissions are waiting for review.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Pending reviews') }}</h6>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-link text-decoration-none">{{ __('View all') }}</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Target') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pendingReviews as $review)
                                <tr>
                                    <td>{{ $review->user?->name ?? '-' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($review->target_label, 28) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.reviews.show', $review) }}" class="btn btn-outline-info btn-sm">{{ __('Moderate') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-4 text-muted">{{ __('No reviews waiting for moderation.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Recent feedback') }}</h6>
                    <a href="{{ route('admin.feedback.index') }}" class="btn btn-sm btn-link text-decoration-none">{{ __('View all') }}</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('From') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Message') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentFeedback as $item)
                                <tr>
                                    <td>{{ $item->user?->name ?? __('Guest') }}</td>
                                    <td>{{ ucfirst($item->type) }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($item->message, 30) }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.feedback.show', $item) }}" class="btn btn-outline-info btn-sm">{{ __('Open') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">{{ __('No feedback yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Upcoming events') }}</h6>
                    <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-link text-decoration-none">{{ __('View all') }}</a>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('City') }}</th>
                                <th>{{ __('Start') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($upcomingEvents as $event)
                                <tr>
                                    <td>{{ $event->title }}</td>
                                    <td>{{ $event->city }}</td>
                                    <td>{{ $event->start_at->format('M d, Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-outline-info btn-sm">{{ __('Edit') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        {{ __('No upcoming events.') }}
                                        <a href="{{ route('admin.events.create') }}" class="d-block mt-1">{{ __('Add Event') }}</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Popular content') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Views') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($popularContent as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->category?->name ?? '-' }}</td>
                                    <td>{{ $item->type }}</td>
                                    <td>{{ number_format($item->view_count) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.contents.show', $item) }}" class="btn btn-outline-info btn-sm">{{ __('View') }}</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">{{ __('No published content yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
