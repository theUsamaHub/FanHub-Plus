@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Content') }}</h2>
            <a href="{{ route('admin.contents.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Add Content') }}
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col"><div class="card border-start border-primary border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Total') }}</div><div class="fw-semibold">{{ $stats['total'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-success border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Published') }}</div><div class="fw-semibold">{{ $stats['published'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-secondary border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Drafts') }}</div><div class="fw-semibold">{{ $stats['draft'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-warning border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Pending Review') }}</div><div class="fw-semibold">{{ $stats['pending'] }}</div></div></div></div>
        <div class="col"><div class="card border-start border-info border-3"><div class="card-body py-2"><div class="text-muted small">{{ __('Featured') }}</div><div class="fw-semibold">{{ $stats['featured'] }}</div></div></div></div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.contents.index') }}" class="row g-2">
                <div class="col-md-3">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search title, excerpt, body...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="category_id">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="type">
                        <option value="">{{ __('All Types') }}</option>
                        @foreach (['article', 'video', 'audio', 'image'] as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">{{ __('All Statuses') }}</option>
                        @foreach (['draft', 'pending_review', 'published', 'rejected'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select" name="featured">
                        <option value="">{{ __('Featured') }}</option>
                        <option value="1" @selected(request('featured') === '1')>{{ __('Yes') }}</option>
                        <option value="0" @selected(request('featured') === '0')>{{ __('No') }}</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select" name="user_submitted">
                        <option value="">{{ __('Source') }}</option>
                        <option value="1" @selected(request('user_submitted') === '1')>{{ __('User') }}</option>
                        <option value="0" @selected(request('user_submitted') === '0')>{{ __('Admin') }}</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select" name="sort">
                        <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ __('Latest') }}</option>
                        <option value="views" @selected(request('sort') === 'views')>{{ __('Views') }}</option>
                        <option value="title" @selected(request('sort') === 'title')>{{ __('Title') }}</option>
                    </select>
                </div>
                <div class="col-md-12 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-search me-1"></i>{{ __('Filter') }}</button>
                    <a href="{{ route('admin.contents.index') }}" class="btn btn-outline-secondary btn-sm">{{ __('Clear') }}</a>
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
                            <th>{{ __('Title') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Featured') }}</th>
                            <th>{{ __('Views') }}</th>
                            <th>{{ __('Release') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contents as $item)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.contents.show', $item) }}" class="fw-medium text-decoration-none">{{ $item->title }}</a>
                                    @if ($item->is_user_submitted)
                                        <span class="badge text-bg-light border ms-1">{{ __('User') }}</span>
                                    @endif
                                </td>
                                <td>{{ $item->category?->name ?? '-' }}</td>
                                <td>{{ $item->type }}</td>
                                <td>
                                    @php $statusClass = ['draft' => 'secondary', 'pending_review' => 'warning', 'published' => 'success', 'rejected' => 'danger'][$item->status] ?? 'secondary'; @endphp
                                    <span class="badge text-bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $item->status)) }}</span>
                                </td>
                                <td>
                                    <form action="{{ route('admin.contents.feature', $item) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm {{ $item->is_featured ? 'btn-warning' : 'btn-outline-secondary' }}" title="{{ __('Toggle featured') }}">
                                            <i class="bi bi-star{{ $item->is_featured ? '-fill' : '' }}"></i>
                                        </button>
                                    </form>
                                </td>
                                <td>{{ number_format($item->view_count) }}</td>
                                <td>{{ $item->release_date?->format('Y-m-d') ?? '-' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.contents.show', $item) }}" class="btn btn-outline-info" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.contents.edit', $item) }}" class="btn btn-outline-primary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.contents.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this content permanently?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <i class="bi bi-file-earmark-text"></i>
                                        <p>{{ __('No content found.') }}</p>
                                        <a href="{{ route('admin.contents.create') }}" class="btn btn-primary btn-sm mt-2">{{ __('Create your first content') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($contents->hasPages())
            <div class="card-footer bg-white">{{ $contents->links() }}</div>
        @endif
    </div>
@endsection
