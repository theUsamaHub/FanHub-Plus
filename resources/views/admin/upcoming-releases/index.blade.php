@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Upcoming Releases') }}</h2>
            <a href="{{ route('admin.upcoming-releases.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Add Release') }}
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-start border-primary border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:0.75rem;">{{ __('Total Releases') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-success border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:0.75rem;">{{ __('Published') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['published'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-warning border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:0.75rem;">{{ __('Still Upcoming') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['upcoming'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-start border-info border-4 h-100">
                <div class="card-body">
                    <div class="text-muted" style="font-size:0.75rem;">{{ __('Events') }}</div>
                    <div class="fs-4 fw-bold">{{ $stats['events'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card fh-adm-filter mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.upcoming-releases.index') }}" class="row g-2">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search title or label...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="kind">
                        <option value="">{{ __('All Kinds') }}</option>
                        @foreach (\App\Models\UpcomingRelease::KINDS as $kind)
                            <option value="{{ $kind }}" @selected(request('kind') === $kind)>{{ ucwords($kind) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="published">
                        <option value="">{{ __('Any Status') }}</option>
                        <option value="1" @selected(request('published') === '1')>{{ __('Published') }}</option>
                        <option value="0" @selected(request('published') === '0')>{{ __('Unpublished') }}</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.upcoming-releases.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
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
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Kind') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Release Date') }}</th>
                            <th>{{ __('Label') }}</th>
                            <th>{{ __('Published') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($releases as $release)
                            <tr>
                                <td class="fw-medium">{{ $release->title }}</td>
                                <td><span class="badge text-bg-light border">{{ $release->kind_label }}</span></td>
                                <td>{{ $release->category?->name ?? '—' }}</td>
                                <td>
                                    @if($release->release_date)
                                        <time datetime="{{ $release->release_date->format('Y-m-d') }}">{{ $release->release_date->format('M d, Y') }}</time>
                                    @else
                                        <span class="text-muted">{{ __('Date TBA') }}</span>
                                    @endif
                                </td>
                                <td>{{ $release->release_label ?? '—' }}</td>
                                <td>
                                    @if($release->is_published)
                                        <span class="badge text-bg-success">{{ __('Yes') }}</span>
                                    @else
                                        <span class="badge text-bg-secondary">{{ __('No') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.upcoming-releases.edit', $release) }}" class="btn btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.upcoming-releases.destroy', $release) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this release?') }}')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">{{ __('No upcoming releases found.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($releases->hasPages())
            <div class="card-footer bg-white">{{ $releases->links() }}</div>
        @endif
    </div>
@endsection
