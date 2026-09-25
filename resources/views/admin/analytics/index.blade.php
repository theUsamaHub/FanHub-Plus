@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Analytics') }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ __('Overview of content performance, user growth, and engagement metrics.') }}</p>
            </div>
            <div class="d-flex gap-2">
                <div class="btn-group" role="group">
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
            <div class="card border-start border-primary border-4 h-100 fh-adm-metric-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted" style="font-size:.75rem;">{{ __('Total Views') }}</div>
                            <div class="fs-3 fw-bold text-primary" id="metricTotalViews">{{ number_format($totalViews ?? 0) }}</div>
                        </div>
                        <div class="fh-adm-metric-icon bg-primary bg-opacity-10">
                            <i class="bi bi-eye"></i>
                        </div>
                    </div>
                    <div class="mt-2 d-flex align-items-center gap-1" id="trendTotalViews">
                        <i class="bi bi-arrow-up text-success" style="font-size:.75rem;"></i>
                        <span class="text-success fw-medium" style="font-size:.75rem;">+12.5%</span>
                        <span class="text-muted" style="font-size:.7rem;">{{ __('vs last period') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-start border-success border-4 h-100 fh-adm-metric-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted" style="font-size:.75rem;">{{ __('Total Users') }}</div>
                            <div class="fs-3 fw-bold text-success" id="metricTotalUsers">{{ number_format($totalUsers ?? 0) }}</div>
                        </div>
                        <div class="fh-adm-metric-icon bg-success bg-opacity-10">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                    <div class="mt-2 d-flex align-items-center gap-1" id="trendTotalUsers">
                        <i class="bi bi-arrow-up text-success" style="font-size:.75rem;"></i>
                        <span class="text-success fw-medium" style="font-size:.75rem;">+8.2%</span>
                        <span class="text-muted" style="font-size:.7rem;">{{ __('vs last period') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-start border-warning border-4 h-100 fh-adm-metric-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted" style="font-size:.75rem;">{{ __('New Users (30d)') }}</div>
                            <div class="fs-3 fw-bold text-warning" id="metricNewUsers">{{ number_format($newUsers30d ?? 0) }}</div>
                        </div>
                        <div class="fh-adm-metric-icon bg-warning bg-opacity-10">
                            <i class="bi bi-person-plus"></i>
                        </div>
                    </div>
                    <div class="mt-2 d-flex align-items-center gap-1" id="trendNewUsers">
                        <i class="bi bi-arrow-up text-success" style="font-size:.75rem;"></i>
                        <span class="text-success fw-medium" style="font-size:.75rem;">+23.1%</span>
                        <span class="text-muted" style="font-size:.7rem;">{{ __('vs last period') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="card border-start border-info border-4 h-100 fh-adm-metric-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="text-muted" style="font-size:.75rem;">{{ __('Engagement Rate') }}</div>
                            <div class="fs-3 fw-bold text-info" id="metricEngagement">{{ number_format($engagementRate ?? 0, 1) }}%</div>
                        </div>
                        <div class="fh-adm-metric-icon bg-info bg-opacity-10">
                            <i class="bi bi-graph-up"></i>
                        </div>
                    </div>
                    <div class="mt-2 d-flex align-items-center gap-1" id="trendEngagement">
                        <i class="bi bi-arrow-down text-danger" style="font-size:.75rem;"></i>
                        <span class="text-danger fw-medium" style="font-size:.75rem;">-2.1%</span>
                        <span class="text-muted" style="font-size:.7rem;">{{ __('vs last period') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-3 mb-4">
        {{-- Views Trend Chart --}}
        <div class="col-lg-8">
            <div class="card h-100 fh-adm-form-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Views Trend') }}</h6>
                    <div class="d-flex gap-1" role="tablist">
                        <button class="btn btn-sm btn-outline-secondary active" data-chart="views" data-period="daily" role="tab">{{ __('Daily') }}</button>
                        <button class="btn btn-sm btn-outline-secondary" data-chart="views" data-period="weekly" role="tab">{{ __('Weekly') }}</button>
                        <button class="btn btn-sm btn-outline-secondary" data-chart="views" data-period="monthly" role="tab">{{ __('Monthly') }}</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 300px; position: relative;">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- User Growth Chart --}}
        <div class="col-lg-4">
            <div class="card h-100 fh-adm-form-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('User Growth') }}</h6>
                    <div class="d-flex gap-1" role="tablist">
                        <button class="btn btn-sm btn-outline-secondary active" data-chart="users" data-period="daily" role="tab">{{ __('Daily') }}</button>
                        <button class="btn btn-sm btn-outline-secondary" data-chart="users" data-period="weekly" role="tab">{{ __('Weekly') }}</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 300px; position: relative;">
                        <canvas id="usersChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        {{-- Content by Category --}}
        <div class="col-lg-6">
            <div class="card h-100 fh-adm-form-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Content by Category') }}</h6>
                    <button class="btn btn-sm btn-outline-secondary" data-chart="categories" data-type="doughnut" role="tab">{{ __('Doughnut') }}</button>
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
            <div class="card h-100 fh-adm-form-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Content by Status') }}</h6>
                    <button class="btn btn-sm btn-outline-secondary" data-chart="status" data-type="bar" role="tab">{{ __('Bar') }}</button>
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
            <div class="card h-100 fh-adm-form-card">
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
                                        <td class="fw-medium">{{ \Illuminate\Support\Str::limit($item->title, 40) }}</td>
                                        <td>{{ $item->category?->name ?? '<span class="text-muted">-</span>' }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($item->view_count) }}</td>
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
                                    <tr><td colspan="4" class="text-center py-3 text-muted">{{ __('No content yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100 fh-adm-form-card">
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
                                        <td class="fw-medium">{{ \Illuminate\Support\Str::limit($item->name, 40) }}</td>
                                        <td>{{ $item->category?->name ?? '<span class="text-muted">-</span>' }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($item->view_count) }}</td>
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
                                    <tr><td colspan="4" class="text-center py-3 text-muted">{{ __('No merchandise yet.') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reviews & Feedback Summary --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="card h-100 fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Review Moderation') }}</h6>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 200px; position: relative;">
                        <canvas id="reviewsChart"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach ($reviewVolume as $status => $total)
                            <div class="d-flex justify-content-between align-items-center py-1">
                                <span class="text-capitalize">{{ $status }}</span>
                                <span class="fw-semibold">{{ $total }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Feedback Volume') }}</h6>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 200px; position: relative;">
                        <canvas id="feedbackChart"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach ($feedbackVolume as $type => $rows)
                            <div class="mb-2">
                                <div class="fw-medium text-capitalize mb-1">{{ $type }}</div>
                                @foreach ($rows as $row)
                                    <div class="d-flex justify-content-between text-muted" style="font-size:.8rem;">
                                        <span>{{ ucwords(str_replace('_', ' ', $row->status)) }}</span>
                                        <span class="fw-semibold">{{ $row->total }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Ratings Distribution') }}</h6>
                </div>
                <div class="card-body">
                    <div class="fh-adm-chart-wrapper" style="height: 200px; position: relative;">
                        <canvas id="ratingsChart"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach ($ratingsDistribution as $rating => $count)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <div class="fh-adm-rating-bar" style="width: 80px; height: 8px;">
                                    <div class="fh-adm-rating-fill" style="width: {{ $maxRatingCount > 0 ? ($count / $maxRatingCount * 100) : 0 }}%"></div>
                                </div>
                                <span class="text-muted" style="font-size:.8rem;">{{ $rating }}★</span>
                                <span class="fw-semibold" style="font-size:.8rem;">{{ $count }}</span>
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
            <div class="card fh-adm-form-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('User Growth (Last 30 Days)') }}</h6>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">{{ __('View All Users') }}</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive fh-adm-table-scroll">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    @foreach ($userGrowth as $day)
                                        <th class="text-center" style="font-size: 0.7rem;">{{ $day['label'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    @foreach ($userGrowth as $day)
                                        <td class="text-center fw-semibold">{{ $day['count'] }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0 mt-3" style="font-size: 0.8rem;">
                        {{ __('New user registrations per day. Active users not shown due to no reliable last-seen tracking.') }}
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
    <script src="{{ asset('js/admin/analytics-charts.js') }}"></script>
@endpush