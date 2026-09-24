@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0 fw-semibold">{{ __('Category Details') }}</h2>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-pencil me-1"></i>{{ __('Edit') }}
                        </a>
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
                        </a>
                    </div>
                </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card fh-adm-form-card">
                <div class="card-body p-4">
                    <table class="table mb-0 fh-adm-detail-table">
                        <tbody>
                            <tr>
                                <td class="fw-semibold" style="width: 200px;">{{ __('Name') }}</td>
                                <td>{{ $category->name }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Slug') }}</td>
                                <td><code>{{ $category->slug }}</code></td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Description') }}</td>
                                <td>{{ $category->description ?: '-' }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Icon') }}</td>
                                <td>
                                    @if ($category->icon_url)
                                        <img src="{{ $category->icon_url }}" alt="{{ $category->name }}" class="img-thumbnail" style="max-height: 60px;">
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Related Records') }}</td>
                                <td>
                                    {{ $category->contents_count }} {{ __('content') }},
                                    {{ $category->character_profiles_count }} {{ __('characters') }},
                                    {{ $category->merchandise_items_count }} {{ __('merchandise') }},
                                    {{ $category->events_count }} {{ __('events') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Created At') }}</td>
                                <td>{{ $category->created_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Updated At') }}</td>
                                <td>{{ $category->updated_at->format('M d, Y H:i:s') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-danger fh-adm-danger-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold text-danger">{{ __('Danger Zone') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted" style="font-size: 0.875rem;">
                        {{ __('Deleting this category will soft-delete it. You can restore it later. Categories with related content cannot be deleted permanently until those records are removed.') }}
                    </p>
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this category?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100">
                            <i class="bi bi-trash me-1"></i>{{ __('Delete Category') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
