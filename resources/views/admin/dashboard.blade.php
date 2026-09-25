@extends('layouts.app')

@section('content')
    @php
        $statBy = fn (string $label) => collect($stats)->firstWhere('label', $label);
        $users = $statBy('Total Users');
        $content = $statBy('Total Content');
        $published = $statBy('Published Content');
        $pendingSubs = $statBy('Pending Submissions');
        $categories = $statBy('Total Categories');
        $events = $statBy('Upcoming Events');
        $reviews = $statBy('Pending Reviews');
        $feedback = $statBy('Open Feedback');
        $media = $statBy('Total Media');

        $sparkValues = collect($chartData)->pluck('users')->map(fn ($v) => (int) $v)->values();
        $sparkMax = max(1, $sparkValues->max() ?: 1);
        $sparkPoints = $sparkValues->map(function ($v, $i) use ($sparkMax, $sparkValues) {
            $x = $sparkValues->count() > 1 ? ($i / ($sparkValues->count() - 1)) * 100 : 50;
            $y = 100 - (($v / $sparkMax) * 88) - 6;
            return sprintf('%.2f,%.2f', $x, $y);
        })->implode(' ');
        $newUsers30 = (int) $sparkValues->sum();

        $bars = collect($chartData)->take(-12)->values();
        $barMax = max(1, $bars->max('users') ?: 1);

        $statusTotal = max(1, collect($contentByStatus)->sum());
        $publishedPct = (int) round((($contentByStatus['published'] ?? 0) / $statusTotal) * 100);
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

    {{-- Hero bento (fixed placement — reference layout) --}}
    <div class="fh-adm-bento fh-adm-bento--hero mb-4">
        {{-- Left column: statistics + two mini cards --}}
        <div class="b-col-left">
            <div class="fh-adm-tile b-stats fh-adm-tile-pad">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                    <div>
                        <h5 class="fw-semibold mb-4">{{ __('Statistics') }}</h5>
                        <div class="d-flex gap-4">
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="dot" style="width:8px;height:8px;border-radius:50%;background:var(--brand-orange);"></span>
                                    <span class="fh-adm-tile-meta">{{ __('Published') }}</span>
                                </div>
                                <div class="fw-semibold fs-5">{{ $published['count'] ?? 0 }}</div>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="dot" style="width:8px;height:8px;border-radius:50%;background:color-mix(in srgb, var(--brand-orange) 28%, transparent);"></span>
                                    <span class="fh-adm-tile-meta">{{ __('Total content') }}</span>
                                </div>
                                <div class="fw-semibold fs-5">{{ $content['count'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="fh-adm-mini-bars" style="width: 42%;">
                        @foreach ($bars as $day)
                            <div class="fh-adm-mini-bars-col" title="{{ $day['label'] }}: {{ $day['users'] }}">
                                <i class="a" style="height: {{ max(6, (int) (($day['users'] / $barMax) * 100)) }}%"></i>
                                <i class="b" style="height: {{ max(4, (int) (($day['users'] / $barMax) * 52)) }}%"></i>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="b-mini-row">
                {{-- Mini A: media balance-style --}}
                <a href="{{ route('admin.media.index') }}" class="fh-adm-tile b-mini-a">
                    <div class="fh-adm-tile-pad flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="fh-adm-tile-row-icon" style="width:30px;height:30px;border-radius:10px;"><i class="bi bi-folder"></i></span>
                            <span class="fh-adm-tile-meta fw-semibold">{{ __('Media') }}</span>
                        </div>
                        <div class="fh-adm-tile-value fh-adm-tile-value--sm">{{ $media['count'] ?? 0 }}</div>
                        <div class="fh-adm-tile-meta">{{ __('files stored') }}</div>
                    </div>
                    <div class="fh-adm-tile-foot">
                        <span class="fw-semibold">{{ __('Open library') }}</span>
                        <i class="bi bi-arrow-right"></i>
                    </div>
                </a>

                {{-- Mini B: published progress (dream-laptop style) --}}
                <div class="fh-adm-tile b-mini-b fh-adm-tile--solid fh-adm-tile--ink fh-adm-tile-pad">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">{{ $published['count'] ?? 0 }}</div>
                            <div class="fh-adm-tile-meta">{{ __('Published') }}</div>
                        </div>
                        <div class="fh-adm-tile-meta text-decoration-line-through" style="opacity:.75;">{{ $content['count'] ?? 0 }}</div>
                    </div>
                    <div class="mt-auto pt-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="small fw-semibold">{{ __('Library completion') }}</span>
                            <span class="small fw-semibold">{{ $publishedPct }}%</span>
                        </div>
                        <div class="fh-adm-progress">
                            <i style="width: {{ max(4, $publishedPct) }}%"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Solid hero (users) --}}
        <a href="{{ route('admin.users.index') }}" class="fh-adm-tile b-hero fh-adm-tile--solid fh-adm-tile--sun fh-adm-tile-pad">
            <div class="d-flex justify-content-between align-items-start">
                <div class="min-w-0">
                    <span class="fh-adm-tile-label">{{ __('Total users') }}</span>
                    <div class="fh-adm-tile-value mt-1">{{ $users['count'] ?? 0 }}</div>
                    <div class="mt-3 d-flex align-items-center gap-3">
                        <span class="badge rounded-pill" style="background: color-mix(in srgb, var(--brand-cream) 22%, transparent); color: inherit; font-weight: 600;">
                            +{{ $newUsers30 }} {{ __('30d') }}
                        </span>
                        <svg style="height:34px;width:140px;overflow:visible;" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                            <polyline points="{{ $sparkPoints }}" fill="none" stroke="var(--brand-cream)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="fh-adm-tile-icon">
                    <i class="bi bi-people"></i>
                </div>
            </div>
        </a>

        {{-- Transaction-style list under hero --}}
        <div class="fh-adm-tile b-list">
            <div class="fh-adm-tile-list">
                <a href="{{ route('admin.submissions.index') }}" class="fh-adm-tile-row">
                    <div class="fh-adm-tile-row-icon"><i class="bi bi-inbox"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">{{ __('Pending submissions') }}</div>
                        <div class="fh-adm-tile-meta">{{ __('Needs review') }}</div>
                    </div>
                    <strong class="fs-5">{{ $pendingSubs['count'] ?? 0 }}</strong>
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="fh-adm-tile-row">
                    <div class="fh-adm-tile-row-icon"><i class="bi bi-chat-left-text"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">{{ __('Pending reviews') }}</div>
                        <div class="fh-adm-tile-meta">{{ __('Moderation queue') }}</div>
                    </div>
                    <strong class="fs-5">{{ $reviews['count'] ?? 0 }}</strong>
                </a>
                <a href="{{ route('admin.feedback.index') }}" class="fh-adm-tile-row">
                    <div class="fh-adm-tile-row-icon"><i class="bi bi-megaphone"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold">{{ __('Open feedback') }}</div>
                        <div class="fh-adm-tile-meta">{{ __('From fans') }}</div>
                    </div>
                    <strong class="fs-5">{{ $feedback['count'] ?? 0 }}</strong>
                </a>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="fh-adm-bento mb-4">
        <div class="fh-adm-tile fh-adm-tile--span12 fh-adm-tile-pad d-flex flex-wrap gap-2">
            <a href="{{ route('admin.contents.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle me-1"></i>{{ __('Add Content') }}</a>
            <a href="{{ route('admin.media.index') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-upload me-1"></i>{{ __('Upload Media') }}</a>
            <a href="{{ route('admin.events.create') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-calendar-plus me-1"></i>{{ __('Add Event') }}</a>
            <a href="{{ route('admin.characters.create') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-person-plus me-1"></i>{{ __('Add Character') }}</a>
            <a href="{{ route('admin.merchandise.create') }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-box-seam me-1"></i>{{ __('Add Merchandise') }}</a>
            <a href="{{ route('admin.submissions.index') }}" class="btn btn-outline-warning btn-sm"><i class="bi bi-inbox me-1"></i>{{ __('Review Submissions') }}</a>
        </div>
    </div>

    {{-- Middle row: chips + status --}}
    <div class="fh-adm-bento mb-4">
        @if ($categories)
            <a href="{{ route($categories['route'], $categories['params'] ?? []) }}" class="fh-adm-tile fh-adm-tile--span4 fh-adm-tile-pad d-flex align-items-center gap-3" data-accent="warning">
                <div class="fh-adm-tile-icon"><i class="bi {{ $categories['icon'] }}"></i></div>
                <div>
                    <div class="fh-adm-tile-value fh-adm-tile-value--sm">{{ $categories['count'] }}</div>
                    <span class="fh-adm-tile-label">{{ $categories['label'] }}</span>
                </div>
            </a>
        @endif
        @if ($events)
            <a href="{{ route($events['route'], $events['params'] ?? []) }}" class="fh-adm-tile fh-adm-tile--span4 fh-adm-tile-pad d-flex align-items-center gap-3" data-accent="primary">
                <div class="fh-adm-tile-icon"><i class="bi {{ $events['icon'] }}"></i></div>
                <div>
                    <div class="fh-adm-tile-value fh-adm-tile-value--sm">{{ $events['count'] }}</div>
                    <span class="fh-adm-tile-label">{{ $events['label'] }}</span>
                </div>
            </a>
        @endif
        <div class="fh-adm-tile fh-adm-tile--span4">
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

    {{-- Guild Intel — rule-based insights --}}
    @if (! empty($insightCards))
        <div class="fh-adm-bento mb-4">
            <div class="fh-adm-tile fh-adm-tile--span12">
                <div class="fh-adm-tile-head">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Guild intel') }}</h6>
                    <span class="fh-adm-chip" data-tone="{{ ($insightSource ?? 'rules') === 'gemini' ? 'accent' : 'muted' }}">
                        {{ ($insightSource ?? 'rules') === 'gemini' ? __('Gemini') : __('Rules') }}
                    </span>
                </div>
                <div class="fh-adm-tile-pad pt-1">
                    <div class="row g-3">
                        @foreach ($insightCards as $card)
                            <div class="col-md-6 col-xl-4">
                                <div class="fh-adm-insight" data-tone="{{ $card['tone'] }}">
                                    <div class="fw-semibold mb-1">{{ $card['title'] }}</div>
                                    <div class="fh-adm-tile-meta">{{ $card['body'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Chart.js data payload --}}
    <script type="application/json" id="fh-dashboard-data">@json($chartPayload ?? [])</script>

    {{-- Charts bento --}}
    <div class="fh-adm-bento mb-4">
        <div class="fh-adm-tile fh-adm-tile--span6 fh-adm-tile--chart">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('User growth · 30 days') }}</h6>
            </div>
            <div class="fh-adm-chart-box">
                <canvas id="chart-user-growth"></canvas>
            </div>
        </div>

        <div class="fh-adm-tile fh-adm-tile--span6 fh-adm-tile--chart">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Users by role') }}</h6>
            </div>
            <div class="fh-adm-chart-box">
                <canvas id="chart-users-role"></canvas>
            </div>
        </div>

        <div class="fh-adm-tile fh-adm-tile--span6 fh-adm-tile--chart">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Content by type') }}</h6>
            </div>
            <div class="fh-adm-chart-box fh-adm-chart-box--sm">
                <canvas id="chart-content-type"></canvas>
            </div>
        </div>

        <div class="fh-adm-tile fh-adm-tile--span6 fh-adm-tile--chart">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Top chatbot questions') }}</h6>
            </div>
            <div class="fh-adm-tile-pad pt-1">
                @php
                    $topQ = collect($chartPayload['chatbotTopQuestions'] ?? []);
                    $topQMax = max(1, (int) $topQ->max('count'));
                @endphp
                @forelse ($topQ as $i => $q)
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="fh-adm-tile-meta fw-semibold" style="width: 22px;">{{ $i + 1 }}</span>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between gap-2">
                                <span class="small text-truncate">{{ $q['label'] }}</span>
                                <span class="small fw-semibold">{{ $q['count'] }}</span>
                            </div>
                            <div class="fh-adm-progress mt-1" style="height: 6px;">
                                <i style="width: {{ max(6, (int) round(($q['count'] / $topQMax) * 100)) }}%"></i>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted py-4 text-center">{{ __('No chatbot queries yet.') }}</div>
                @endforelse
            </div>
        </div>

        <div class="fh-adm-tile fh-adm-tile--span6 fh-adm-tile--chart">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Content by status') }}</h6>
            </div>
            <div class="fh-adm-chart-box fh-adm-chart-box--sm">
                <canvas id="chart-content-status"></canvas>
            </div>
        </div>

        <div class="fh-adm-tile fh-adm-tile--span6 fh-adm-tile--chart">
            <div class="fh-adm-tile-head">
                <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Reviews by status') }}</h6>
            </div>
            <div class="fh-adm-chart-box fh-adm-chart-box--sm">
                <canvas id="chart-reviews-status"></canvas>
            </div>
        </div>
    </div>

    {{-- Lists --}}
    <div class="fh-adm-bento">
        <div class="fh-adm-tile fh-adm-tile--span6">
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

        <div class="fh-adm-tile fh-adm-tile--span6">
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

        <div class="fh-adm-tile fh-adm-tile--span6">
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

        <div class="fh-adm-tile fh-adm-tile--span6">
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

        <div class="fh-adm-tile fh-adm-tile--span12">
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

@push('scripts')
    @vite('resources/js/pages/admin-dashboard.js')
@endpush
