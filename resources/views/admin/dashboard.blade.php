@extends('layouts.app')

@section('content')
    @php
        $heroLabels = ['Total Users', 'Total Content', 'Pending Submissions'];
        $heroStats = collect($stats)->filter(fn ($s) => in_array($s['label'], $heroLabels, true))->values();
        $chipStats = collect($stats)->reject(fn ($s) => in_array($s['label'], $heroLabels, true))->values();

        $sparkValues = collect($chartData)->pluck('users')->map(fn ($v) => (int) $v)->values();
        $sparkMax = max(1, $sparkValues->max() ?: 1);
        $sparkPoints = $sparkValues->map(function ($v, $i) use ($sparkMax, $sparkValues) {
            $x = $sparkValues->count() > 1 ? ($i / ($sparkValues->count() - 1)) * 100 : 50;
            $y = 100 - (($v / $sparkMax) * 88) - 6;
            return sprintf('%.2f,%.2f', $x, $y);
        })->implode(' ');
        $sparkArea = '0,100 '.$sparkPoints.' 100,100';
        $newUsers30 = (int) $sparkValues->sum();
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-0 fw-semibold">{{ __('Admin Dashboard') }}</h2>
            <p class="text-muted mb-0" style="font-size: 0.875rem;">{{ now()->format('l, M j, Y') }}</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-clockwise me-1"></i>{{ __('Refresh') }}
        </a>
    </div>

    <div class="fh-adm-bento mb-4">
        @foreach ($heroStats as $stat)
            <a href="{{ route($stat['route'], $stat['params'] ?? []) }}" class="fh-adm-tile fh-adm-tile--hero" data-accent="{{ $stat['color'] }}">
                <div class="d-flex justify-content-between align-items-start gap-3">
                    <div class="min-w-0">
                        <span class="fh-adm-tile-hero-label">{{ $stat['label'] }}</span>
                        <div class="fh-adm-tile-hero-value">{{ $stat['count'] }}</div>
                    </div>
                    <div class="fh-adm-tile-icon">
                        <i class="bi {{ $stat['icon'] }}"></i>
                    </div>
                </div>
            </a>
        @endforeach

        <div class="fh-adm-tile fh-adm-tile--spark">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <span class="fh-adm-tile-hero-label">{{ __('New users · last 30 days') }}</span>
                    <div class="fh-adm-tile-hero-value">{{ $newUsers30 }}</div>
                </div>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm align-self-center">{{ __('Users') }}</a>
            </div>
            <svg class="fh-adm-spark mt-3" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <polygon class="fh-adm-spark-area" points="{{ $sparkArea }}"></polygon>
                <polyline class="fh-adm-spark-line" points="{{ $sparkPoints }}"></polyline>
            </svg>
            <div class="d-flex justify-content-between" style="font-size: 0.68rem; color: var(--fh-adm-dim);">
                <span>{{ collect($chartData)->first()['label'] ?? '' }}</span>
                <span>{{ collect($chartData)->last()['label'] ?? '' }}</span>
            </div>
        </div>

        @foreach ($chipStats as $stat)
            <a href="{{ route($stat['route'], $stat['params'] ?? []) }}" class="fh-adm-tile fh-adm-tile--chip" data-accent="{{ $stat['color'] }}">
                <div class="fh-adm-tile-icon">
                    <i class="bi {{ $stat['icon'] }}"></i>
                </div>
                <div class="min-w-0">
                    <div class="fh-adm-tile-chip-value">{{ $stat['count'] }}</div>
                    <span class="fh-adm-tile-chip-label">{{ $stat['label'] }}</span>
                </div>
            </a>
        @endforeach

        <div class="fh-adm-tile fh-adm-tile--side">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Content by status') }}</h6>
            </div>
            <div class="fh-adm-tile-body">
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

    <div class="fh-adm-bento">
        <div class="fh-adm-tile fh-adm-tile--half">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Recent user submissions') }}</h6>
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-sm btn-link text-decoration-none">{{ __('View all') }}</a>
            </div>
            <div class="fh-adm-tile-body">
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

        <div class="fh-adm-tile fh-adm-tile--half">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Pending reviews') }}</h6>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-sm btn-link text-decoration-none">{{ __('View all') }}</a>
            </div>
            <div class="fh-adm-tile-body">
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

        <div class="fh-adm-tile fh-adm-tile--half">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Recent feedback') }}</h6>
                <a href="{{ route('admin.feedback.index') }}" class="btn btn-sm btn-link text-decoration-none">{{ __('View all') }}</a>
            </div>
            <div class="fh-adm-tile-body">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('From') }}</th>
                            <th>{{ __('Message') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentFeedback as $item)
                            <tr>
                                <td>{{ $item->user?->name ?? __('Guest') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($item->message, 36) }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $item->status)) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.feedback.show', $item) }}" class="btn btn-outline-info btn-sm">{{ __('Open') }}</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">{{ __('No feedback yet.') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="fh-adm-tile fh-adm-tile--half">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Upcoming events') }}</h6>
                <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-link text-decoration-none">{{ __('View all') }}</a>
            </div>
            <div class="fh-adm-tile-body">
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

        <div class="fh-adm-tile fh-adm-tile--full">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Popular content') }}</h6>
            </div>
            <div class="fh-adm-tile-body">
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
@endsection
