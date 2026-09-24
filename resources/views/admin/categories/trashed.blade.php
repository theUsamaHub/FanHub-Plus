@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0 fw-semibold">{{ __('Trash') }}</h2>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>{{ __('Back to Categories') }}
                    </a>
                </div>
    </div>

    <div class="card fh-adm-form-card">
        <div class="card-body p-0">
            <div class="table-responsive fh-adm-table-scroll">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Slug') }}</th>
                            <th>{{ __('Deleted At') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td class="fw-medium">{{ $category->name }}</td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td class="text-muted">{{ $category->deleted_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <form action="{{ route('admin.categories.restore', $category->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success" title="{{ __('Restore') }}" onclick="return confirm('{{ __('Restore this category?') }}')">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.categories.force-delete', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Permanently delete this category? This cannot be undone.') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete Permanently') }}">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5">
                                    <div class="fh-adm-empty">
                                        <i class="bi bi-trash"></i>
                                        <p>{{ __('Trash is empty.') }}</p>
                                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm mt-2">
                                            {{ __('Go to Categories') }}
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
