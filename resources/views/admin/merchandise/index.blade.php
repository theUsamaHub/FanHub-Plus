@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Merchandise') }}</h2>
            <a href="{{ route('admin.merchandise.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Add Merchandise') }}
            </a>
        </div>
    </div>

    <div class="card fh-adm-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.merchandise.index') }}" class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search merchandise...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="category_id">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="tag">
                        <option value="">{{ __('All Tags') }}</option>
                        @foreach (['limited_edition', 'pre_order', 'collectible', 'standard'] as $tagOption)
                            <option value="{{ $tagOption }}" @selected(request('tag') === $tagOption)>{{ ucwords(str_replace('_', ' ', $tagOption)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="upcoming">
                        <option value="">{{ __('Upcoming') }}</option>
                        <option value="1" @selected(request('upcoming') === '1')>{{ __('Yes') }}</option>
                        <option value="0" @selected(request('upcoming') === '0')>{{ __('No') }}</option>
                    </select>
                </div>
                <div class="col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary">{{ __('Filter') }}</button>
                    <a href="{{ route('admin.merchandise.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card fh-adm-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Tag') }}</th>
                            <th>{{ __('Upcoming') }}</th>
                            <th>{{ __('Views') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td class="fw-medium">{{ $item->name }}</td>
                                <td>{{ $item->category?->name ?? '-' }}</td>
                                <td><span class="fh-adm-chip" data-tone="accent">{{ ucwords(str_replace('_', ' ', $item->tag)) }}</span></td>
                                <td>{{ $item->is_upcoming ? __('Yes') : __('No') }}</td>
                                <td>{{ number_format($item->view_count) }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.merchandise.show', $item) }}" class="btn btn-outline-info" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.merchandise.edit', $item) }}" class="btn btn-outline-primary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.merchandise.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this merchandise item?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="fh-adm-empty">
                                        <i class="bi bi-box-seam"></i>
                                        <p>{{ __('No merchandise found.') }}</p>
                                        <a href="{{ route('admin.merchandise.create') }}" class="btn btn-primary btn-sm mt-2">{{ __('Add your first merchandise item') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($items->hasPages())
            <div class="card-footer bg-white">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
