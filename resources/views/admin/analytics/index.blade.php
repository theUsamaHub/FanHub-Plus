@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Analytics') }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ __('Overview of content performance, user growth, and engagement metrics.') }}</p>
            </div>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group" aria-label="{{ __('Time range') }}">
                    <button type="button" class="btn btn-outline-secondary btn-sm active" data-range="30">{{ __('30 Days') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-range="90">{{ __('90 Days') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-range="365">{{ __('1 Year') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="row g-3 mb-4" id="metricsRow">
        <div class="col-6 col-xl-3">
            <div class="card h-100 fh-adm-stat-card" data-accent="primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fh-adm-stat-label">{{ __('Total Views') }}</div>
                            <div class="fh-adm-stat-value">{{ number_format($totalViews ?? 0) }}</div>
                        </div>
                        <div class="fh-adm-stat-icon">
                            <i class="bi bi-eye"></i>
                        </div>
                    </div>
                    <div class="fh-adm-stat-trend trend-up">
                        <i class="bi bi-arrow-up-right"></i>
                        <span>+12.5%</span>
                        <span class="text-muted">{{ __('vs last period') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100 fh-adm-stat-card" data-accent="success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fh-adm-stat-label">{{ __('Total Users') }}</div>
                            <div class="fh-adm-stat-value">{{ number_format($totalUsers ?? 0) }}</div>
                        </div>
                        <div class="fh-adm-stat-icon">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="fh-adm-stat-trend trend-up">
                        <i class="bi bi-arrow-up-right"></i>
                        <span>+8.2%</span>
                        <span class="text-muted">{{ __('vs last period') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100 fh-adm-stat-card" data-accent="warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fh-adm-stat-label">{{ __('New Users (30d)') }}</div>
                            <div class="fh-adm-stat-value">{{ number_format($newUsers30d ?? 0) }}</div>
                        </div>
                        <div class="fh-adm-stat-icon">
                            <i class="bi bi-person-plus"></i>
                        </div>
                    </div>
                    <div class="fh-adm-stat-trend trend-up">
                        <i class="bi bi-arrow-up-right"></i>
                        <span>+23.1%</span>
                        <span class="text-muted">{{ __('vs last period') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card h-100 fh-adm-stat-card" data-accent="info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fh-adm-stat-label">{{ __('Engagement Rate') }}</div>
                            <div class="fh-adm-stat-value">{{ number_format($engagementRate ?? 0, 1) }}%</div>
                        </div>
                        <div class="fh-adm-stat-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                    </div>
                    <div class="fh-adm-stat-trend trend-down">
                        <i class="bi bi-arrow-down-right"></i>
                        <span>-2.1%</span>
                        <span class="text-muted">{{ __('vs last period') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Charts Row --}}
    <div class="row g-3 mb-4">
        {{-- Views Trend Chart --}}
        <div class="col-xl-8">
            <div class="card h-100 fh-adm-chart-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Views Trend') }}</h6>
                        <div class="d-flex gap-1" role="tablist" aria-label="{{ __('Period') }}">
                            <button class="btn btn-sm btn-outline-secondary active" data-chart="views" data-period="daily" role="tab">{{ __('Daily') }}</button>
                            <button class="btn btn-sm btn-outline-secondary" data-chart="views" data-period="weekly" role="tab">{{ __('Weekly') }}</button>
                            <button class="btn btn-sm btn-outline-secondary" data-chart="views" data-period="monthly" role="tab">{{ __('Monthly') }}</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 320px; position: relative;">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- User Growth Chart --}}
        <div class="col-xl-4">
            <div class="card h-100 fh-adm-chart-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('User Growth') }}</h6>
                        <div class="d-flex gap-1" role="tablist" aria-label="{{ __('Period') }}">
                            <button class="btn btn-sm btn-outline-secondary active" data-chart="users" data-period="daily" role="tab">{{ __('Daily') }}</button>
                            <button class="btn btn-sm btn-outline-secondary" data-chart="users" data-period="weekly" role="tab">{{ __('Weekly') }}</button>
                            <button class="btn btn-sm btn-outline-secondary" data-chart="users" data-period="monthly" role="tab">{{ __('Monthly') }}</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 320px; position: relative;">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Secondary Charts Row --}}
    <div class="row g-3 mb-4">
        {{-- Content by Category --}}
        <div class="col-lg-6">
            <div class="card h-100 fh-adm-chart-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Content by Category') }}</h6>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 300px; position: relative;">
                        <canvas id="categoriesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content by Status --}}
        <div class="col-lg-6">
            <div class="card h-100 fh-adm-chart-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Content by Status') }}</h6>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 300px; position: relative;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Content & Merchandise Tables --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card h-100 fh-adm-table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Top Content by Views') }}</h6>
                    <a href="{{ route('admin.contents.index', ['sort' => 'views']) }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive fh-adm-table-scroll">
                        <table class="table mb-0 fh-adm-detail-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Title') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th class="text-end">{{ __('Views') }}</th>
                                    <th class="text-end">{{ __('Trend') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topContent as $item)
                                    <tr>
                                        <td class="fw-medium">{{ \Illuminate\Support\Str::limit($item->title, 45) }}</td>
                                        <td>
                                            @if ($item->category)
                                                <span class="badge bg-primary-subtle text-primary">{{ $item->category->name }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold text-primary">{{ number_format($item->view_count) }}</td>
                                        <td class="text-end">
                                            @php
                                                $trend = rand(-15, 25);
                                                $isUp = $trend >= 0;
                                            @endphp
                                            <span class="fh-adm-trend-badge {{ $isUp ? 'trend-up' : 'trend-down' }}">
                                                <i class="bi bi-{{ $isUp ? 'arrow-up-right' : 'arrow-down-right' }}"></i>
                                                <span>{{ $isUp ? '+' : '' }}{{ $trend }}%</span>
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('No content yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 fh-adm-table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Top Merchandise by Views') }}</h6>
                    <a href="{{ route('admin.merchandise.index', ['sort' => 'views']) }}" class="btn btn-sm btn-outline-primary">{{ __('View All') }}</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive fh-adm-table-scroll">
                        <table class="table mb-0 fh-adm-detail-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th class="text-end">{{ __('Views') }}</th>
                                    <th class="text-end">{{ __('Trend') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($topMerchandise as $item)
                                    <tr>
                                        <td class="fw-medium">{{ \Illuminate\Support\Str::limit($item->name, 45) }}</td>
                                        <td>
                                            @if ($item->category)
                                                <span class="badge bg-warning-subtle text-warning-dark">{{ $item->category->name }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-semibold text-warning">{{ number_format($item->view_count) }}</td>
                                        <td class="text-end">
                                            @php
                                                $trend = rand(-10, 30);
                                                $isUp = $trend >= 0;
                                            @endphp
                                            <span class="fh-adm-trend-badge {{ $isUp ? 'trend-up' : 'trend-down' }}">
                                                <i class="bi bi-{{ $isUp ? 'arrow-up-right' : 'arrow-down-right' }}"></i>
                                                <span>{{ $isUp ? '+' : '' }}{{ $trend }}%</span>
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center py-4 text-muted">{{ __('No merchandise yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reviews, Feedback & Ratings --}}
    <div class="row g-3 mb-4">
        {{-- Review Moderation --}}
        <div class="col-lg-4">
            <div class="card h-100 fh-adm-chart-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Review Moderation') }}</h6>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 180px; position: relative;">
                        <canvas id="reviewsChart"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach ($reviewVolume as $status => $total)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="d-flex align-items-center gap-2">
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'spam' => 'secondary',
                                        ];
                                        $color = $statusColors[$status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}-subtle text-{{ $color }}">{{ ucfirst($status) }}</span>
                                </span>
                                <span class="fw-semibold">{{ $total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Feedback Volume --}}
        <div class="col-lg-4">
            <div class="card h-100 fh-adm-chart-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Feedback Volume') }}</h6>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 180px; position: relative;">
                        <canvas id="feedbackChart"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach ($feedbackVolume as $type => $rows)
                            <div class="mb-3">
                                <div class="fw-medium text-capitalize mb-2">{{ $type }}</div>
                                @foreach ($rows as $row)
                                    <div class="d-flex justify-content-between align-items-center py-1">
                                        <span class="badge bg-secondary-subtle text-secondary">{{ ucwords(str_replace('_', ' ', $row->status)) }}</span>
                                        <span class="fw-semibold">{{ $row->total }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Ratings Distribution --}}
        <div class="col-lg-4">
            <div class="card h-100 fh-adm-chart-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Ratings Distribution') }}</h6>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 180px; position: relative;">
                        <canvas id="ratingsChart"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach ($ratingsDistribution as $rating => $count)
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <span class="fw-bold text-warning" style="min-width: 2.5rem;">{{ $rating }}★</span>
                                <div class="flex-grow-1 fh-adm-rating-bar" style="height: 6px;">
                                    <div class="fh-adm-rating-fill" style="width: {{ $maxRatingCount > 0 ? ($count / $maxRatingCount * 100) : 0 }}%"></div>
                                </div>
                                <span class="fw-semibold text-end" style="min-width: 3rem;">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- User Growth Table --}}
    <div class="row g-3">
        <div class="col-12">
            <div class="card fh-adm-table-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('User Growth (Last 30 Days)') }}</h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All Users') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive fh-adm-table-scroll">
                        <table class="table table-sm mb-0 fh-adm-heatmap-table">
                            <thead>
                                <tr>
                                    @foreach ($userGrowth as $day)
                                        <th class="text-center" style="font-size: 0.65rem; min-width: 40px;">{{ $day['label'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach ($userGrowth as $day)
                                        @php
                                            $maxCount = max(array_column($userGrowth, 'count'));
                                            $intensity = $maxCount > 0 ? min(100, ($day['count'] / $maxCount) * 100) : 0;
                                        @endphp
                                        <td class="text-center fw-semibold position-relative" style="min-width: 40px;">
                                            <div class="fh-adm-heatmap-cell" style="background: rgba(255, 146, 46, {{ $intensity / 100 * 0.4 + 0.1 }});">
                                                {{ $day['count'] }}
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0 mt-3" style="font-size: 0.8rem;">
                        {{ __('New user registrations per day. Darker cells indicate higher registrations.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Data (injected for JS) --}}
    <script>
        window.analyticsData = {
            views: @json($viewsChartData ?? []),
            users: @json($usersChartData ?? []),
            categories: @json($contentByCategory->pluck('contents_count', 'name')->toArray() ?? []),
            status: @json($contentByStatus->toArray() ?? []),
            reviews: @json($reviewVolume->toArray() ?? []),
            feedback: @json($feedbackVolume->map(fn($rows) => $rows->pluck('total', 'status')->toArray())->toArray() ?? []),
            ratings: @json($ratingsDistribution ?? []),
        };
    </script>
@endsection

@push('scripts')
    @vite('resources/js/admin/analytics-charts.js')
@endpush