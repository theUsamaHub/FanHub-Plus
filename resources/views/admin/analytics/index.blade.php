@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Analytics') }}</h2>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Top content by views') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0 fh-adm-detail-table">
                        <thead>
                            <tr>
                                <th>{{ __('Title') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Views') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topContent as $item)
                                <tr>
                                    <td>{{ $item->title }}</td>
                                    <td>{{ $item->category?->name ?? '-' }}</td>
                                    <td>{{ number_format($item->view_count) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-3 text-muted">{{ __('No content yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Top merchandise by views') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0 fh-adm-detail-table">
                        <thead>
                            <tr>
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Views') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($topMerchandise as $item)
                                <tr>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ $item->category?->name ?? '-' }}</td>
                                    <td>{{ number_format($item->view_count) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-3 text-muted">{{ __('No merchandise yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Content by category') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0 fh-adm-detail-table">
                        <thead>
                            <tr>
                                <th>{{ __('Category') }}</th>
                                <th>{{ __('Content count') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contentByCategory as $category)
                                <tr>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->contents_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center py-3 text-muted">{{ __('No categories yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Content by status') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0 fh-adm-detail-table">
                        <thead>
                            <tr>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Count') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contentByStatus as $status => $total)
                                <tr>
                                    <td>{{ ucwords(str_replace('_', ' ', $status)) }}</td>
                                    <td>{{ $total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center py-3 text-muted">{{ __('No content yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Review moderation volume') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0 fh-adm-detail-table">
                        <thead>
                            <tr>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Count') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($reviewVolume as $status => $total)
                                <tr>
                                    <td>{{ ucfirst($status) }}</td>
                                    <td>{{ $total }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center py-3 text-muted">{{ __('No reviews yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Feedback volume') }}</h6></div>
                <div class="card-body p-0">
                    <table class="table mb-0 fh-adm-detail-table">
                        <thead>
                            <tr>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Count') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($feedbackVolume as $type => $rows)
                                @foreach ($rows as $row)
                                    <tr>
                                        <td>{{ ucfirst($type) }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $row->status)) }}</td>
                                        <td>{{ $row->total }}</td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr><td colspan="3" class="text-center py-3 text-muted">{{ __('No feedback yet.') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card fh-adm-table-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('User growth (last 30 days)') }}</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
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
                                        <td class="text-center">{{ $day['count'] }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mb-0 mt-3" style="font-size: 0.8rem;">
                        {{ __('Active users are not shown because the schema has no reliable last-seen tracking.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
