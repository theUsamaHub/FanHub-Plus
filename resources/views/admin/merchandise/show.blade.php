@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ $item->name }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.merchandise.edit', $item) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>{{ __('Edit') }}</a>
                <a href="{{ route('admin.merchandise.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card fh-adm-detail-card">
                <div class="card-body">
                    <table class="table mb-0 fh-adm-detail-table">
                        <tbody>
                            <tr>
                                <td class="fw-semibold" style="width:180px;">{{ __('Image') }}</td>
                                <td>
                                    @if ($item->imageMedia)
                                        @if ($item->imageMedia?->url)
                                        <img src="{{ $item->imageMedia->url }}" alt="{{ $item->name }}" class="img-thumbnail" style="max-height: 140px;">
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">{{ __('Name') }}</td><td>{{ $item->name }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Slug') }}</td><td><code>{{ $item->slug }}</code></td></tr>
                            <tr><td class="fw-semibold">{{ __('Category') }}</td><td>{{ $item->category?->name ?? '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Tag') }}</td><td>{{ ucwords(str_replace('_', ' ', $item->tag)) }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Upcoming') }}</td><td>{{ $item->is_upcoming ? __('Yes') : __('No') }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Views') }}</td><td>{{ number_format($item->view_count) }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Description') }}</td><td style="white-space: pre-wrap;">{{ $item->description ?: '-' }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-danger fh-adm-danger-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold text-danger">{{ __('Danger Zone') }}</h6></div>
                <div class="card-body">
                    <form action="{{ route('admin.merchandise.destroy', $item) }}" method="POST" onsubmit="return confirm('{{ __('Delete this merchandise item?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>{{ __('Delete Merchandise') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
