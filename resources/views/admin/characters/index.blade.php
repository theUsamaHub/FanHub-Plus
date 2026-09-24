@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Characters') }}</h2>
            <a href="{{ route('admin.characters.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>{{ __('Add Character') }}
            </a>
        </div>
    </div>

    <div class="card fh-adm-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.characters.index') }}" class="row g-2">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search characters...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="category_id">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search me-1"></i>{{ __('Filter') }}</button>
                    <a href="{{ route('admin.characters.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
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
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Slug') }}</th>
                            <th>{{ __('Related Content') }}</th>
                            <th class="text-end">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($characters as $character)
                            <tr>
                                <td class="fw-medium">
                                    @if ($character->image_url ?? false)
                                        <img src="{{ $character->imageMedia?->url }}" alt="" class="rounded me-1" style="width:28px;height:28px;object-fit:cover;">
                                    @endif
                                    {{ $character->name }}
                                </td>
                                <td>{{ $character->category?->name ?? '-' }}</td>
                                <td><code>{{ $character->slug }}</code></td>
                                <td>{{ $character->contents_count }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.characters.show', $character) }}" class="btn btn-outline-info" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.characters.edit', $character) }}" class="btn btn-outline-primary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.characters.destroy', $character) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this character? Related content links will be removed.') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="fh-adm-empty">
                                        <i class="bi bi-person-badge"></i>
                                        <p>{{ __('No characters found.') }}</p>
                                        <a href="{{ route('admin.characters.create') }}" class="btn btn-primary btn-sm mt-2">{{ __('Create your first character') }}</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($characters->hasPages())
            <div class="card-footer bg-white">{{ $characters->links() }}</div>
        @endif
    </div>
@endsection
