@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Activity Log') }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ __('Every recorded change across the platform, with a readable summary of what changed.') }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.activity-logs.export', array_filter($filters)) }}" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-download me-1"></i>{{ __('Export CSV') }}
                </a>
                <form action="{{ route('admin.activity-logs.destroy') }}" method="POST" onsubmit="return confirm('{{ __('Permanently delete every activity log? This cannot be undone.') }}')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-trash me-1"></i>{{ __('Clear Logs') }}</button>
                </form>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-4">
            <div class="card border-start border-secondary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Total Entries') }}</div>
                    <div class="fs-4 fw-bold">{{ number_format($stats['total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Today') }}</div>
                    <div class="fs-4 fw-bold">{{ number_format($stats['today']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:.75rem;">{{ __('Last 7 Days') }}</div>
                    <div class="fs-4 fw-bold">{{ number_format($stats['week']) }}</div>
                </div>
            </div>
        </div>
    </div>

<div class="row g-3 mb-4">
        {{-- Event breakdown --}}
        <div class="col-12">
            <div class="card h-100 fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Activity by Type') }}</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($events as $key => [$label, $tone, $icon])
                            <a href="{{ route('admin.activity-logs.index', array_merge(array_filter($filters), ['event' => $key])) }}"
                               class="fh-adm-chip {{ request('event') === $key ? '' : 'text-decoration-none' }}"
                               data-tone="{{ $tone }}" style="padding:6px 12px;">
                                 <i class="bi {{ $icon }}"></i>{{ $label }}
                                 <strong>{{ number_format($stats['events'][$key] ?? 0) }}</strong>
                            </a>
                        @endforeach
                    </div>

                    @if ($stats['types'])
                        <hr class="my-3">
                        <div class="text-muted mb-2" style="font-size:.75rem;">{{ __('Most Changed Records') }}</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($stats['types'] as $type => $meta)
                                <a href="{{ route('admin.activity-logs.index', array_merge(array_filter($filters), ['type' => $type])) }}"
                                   class="fh-adm-chip text-decoration-none {{ request('type') === $type ? '' : '' }}" data-tone="accent" style="padding:5px 11px;">
                                     {{ $meta['label'] }} <strong>{{ number_format($meta['count']) }}</strong>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4 fh-adm-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" value="{{ $filters['search'] }}"
                           placeholder="{{ __('Search record, summary, actor or email...') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="event">
                        <option value="">{{ __('All Events') }}</option>
                        @foreach ($events as $key => [$label])
                            <option value="{{ $key }}" @selected($filters['event'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="type">
                        <option value="">{{ __('All Records') }}</option>
                        @foreach ($types as $class => $label)
                            <option value="{{ $class }}" @selected($filters['type'] === $class)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="user_id">
                        <option value="">{{ __('Everyone') }}</option>
                        @foreach ($actors as $actor)
                            <option value="{{ $actor->id }}" @selected((int) $filters['user_id'] === $actor->id)>{{ $actor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <input type="date" class="form-control" name="from" value="{{ $filters['from'] }}" aria-label="{{ __('From') }}">
                </div>
                <div class="col-md-1">
                    <input type="date" class="form-control" name="to" value="{{ $filters['to'] }}" aria-label="{{ __('To') }}">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-outline-secondary flex-grow-1"><i class="bi bi-search me-1"></i>{{ __('Apply Filters') }}</button>
                    @if ($hasFilters)
                        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-danger"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Log list --}}
    <div class="card fh-adm-table-card">
        <div class="card-body p-0">
            <div class="table-responsive fh-adm-table-scroll">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width:130px;">{{ __('When') }}</th>
                            <th style="width:150px;">{{ __('Who') }}</th>
                            <th style="width:150px;">{{ __('Action') }}</th>
                            <th>{{ __('What Changed') }}</th>
                            <th style="width:110px;" class="text-end">{{ __('Details') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td class="text-muted" style="font-size:.78rem;">
                                    <span title="{{ $log->created_at->format('M j, Y \a\t g:i A') }}">{{ $log->created_at->diffForHumans() }}</span>
                                    <div style="font-size:.7rem; opacity:.7;">{{ $log->created_at->format('M j, g:i A') }}</div>
                                </td>
                                <td style="font-size:.82rem;">
                                    @if ($log->user)
                                        <span class="d-inline-flex align-items-center">
                                            <i class="bi bi-person-circle me-1 opacity-50"></i>{{ \Illuminate\Support\Str::limit($log->user->name, 18) }}
                                        </span>
                                    @else
                                        <span class="fh-adm-chip" data-tone="muted">{{ __('System') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fh-adm-chip" data-tone="{{ $log->event_tone }}">
                                        <i class="bi {{ $log->event_icon }}"></i>{{ $log->event_label }}
                                    </span>
                                    <div class="text-muted mt-1" style="font-size:.7rem;">{{ $log->model_label }}</div>
                                </td>
                                <td>
                                    <div class="fw-medium" style="font-size:.86rem;">{{ $log->description }}</div>
                                    @if ($log->summary)
                                        <div class="fh-adm-diff mt-1">
                                            @foreach (collect($log->changes)->reject(fn ($c) => $c['type'] === 'unchanged')->take(3) as $change)
                                                <span class="fh-adm-diff__row" data-type="{{ $change['type'] }}">
                                                    <span class="fh-adm-diff__field">{{ $change['label'] }}</span>
                                                    @if ($change['type'] === 'removed')
                                                        <span class="fh-adm-diff__old">{{ $logger->formatValue($change['old'], 34) }}</span>
                                                    @elseif ($change['type'] === 'added')
                                                        <span class="fh-adm-diff__new">{{ $logger->formatValue($change['new'], 34) }}</span>
                                                    @else
                                                        <span class="fh-adm-diff__old">{{ $logger->formatValue($change['old'], 28) }}</span>
                                                        <i class="bi bi-arrow-right fh-adm-diff__arrow"></i>
                                                        <span class="fh-adm-diff__new">{{ $logger->formatValue($change['new'], 28) }}</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                            @if ($log->change_count > 3)
                                                <span class="fh-adm-diff__more">+{{ $log->change_count - 3 }} {{ __('more') }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('admin.activity-logs.show', $log) }}" class="btn btn-sm btn-outline-primary" title="{{ __('View full change') }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="bi bi-inbox d-block mb-2 opacity-50" style="font-size:2rem;"></i>
                                    <p class="mb-1 fw-semibold">{{ $hasFilters ? __('No entries match these filters.') : __('No activity recorded yet.') }}</p>
                                    @if ($hasFilters)
                                        <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Clear filters') }}</a>
                                    @else
                                        <p class="text-muted mb-0" style="font-size:.8rem;">{{ __('Changes to stories, events, merchandise, characters and more will appear here.') }}</p>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($logs->hasPages())
            <div class="card-footer bg-white">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
