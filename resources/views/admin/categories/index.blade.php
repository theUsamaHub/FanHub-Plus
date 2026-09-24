@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0 fw-semibold">{{ __('Categories') }}</h2>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.categories.trashed') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-trash me-1"></i>{{ __('Trash') }}
                        </a>
                        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-circle me-1"></i>{{ __('Add Category') }}
                        </a>
                    </div>
                </div>
    </div>

    <!-- Search -->
    <div class="card mb-4 fh-adm-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="row g-3">
                <div class="col-md-9">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search categories...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-search me-1"></i>{{ __('Filter') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories Table -->
    <div class="card fh-adm-table-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Slug') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th>{{ __('Created') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="fw-medium">{{ $category->name }}</td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td class="text-muted">{{ \Illuminate\Support\Str::limit($category->description, 60) ?: '-' }}</td>
                                <td class="text-muted">{{ $category->created_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-outline-info" title="{{ __('View') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-outline-primary" title="{{ __('Edit') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this category?') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="fh-adm-empty">
                                        <i class="bi bi-tags"></i>
                                        <p>{{ __('No categories found.') }}</p>
                                        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm mt-2">
                                            {{ __('Create your first category') }}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($categories->hasPages())
            <div class="card-footer bg-white">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection
