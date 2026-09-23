@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ $content->title }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.contents.edit', $content) }}" class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil me-1"></i>{{ __('Edit') }}</a>
                <a href="{{ route('admin.contents.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td class="fw-semibold" style="width:180px;">{{ __('Slug') }}</td><td><code>{{ $content->slug }}</code></td></tr>
                            <tr><td class="fw-semibold">{{ __('Category') }}</td><td>{{ $content->category?->name ?? '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Type') }}</td><td>{{ $content->type }}</td></tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Status') }}</td>
                                <td>
                                    @php $statusClass = ['draft' => 'secondary', 'pending_review' => 'warning', 'published' => 'success', 'rejected' => 'danger'][$content->status] ?? 'secondary'; @endphp
                                    <span class="badge text-bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $content->status)) }}</span>
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">{{ __('Featured') }}</td><td>{{ $content->is_featured ? __('Yes') : __('No') }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Views') }}</td><td>{{ number_format($content->view_count) }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Popularity') }}</td><td>{{ $content->popularity_score }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Release date') }}</td><td>{{ $content->release_date?->format('Y-m-d') ?? '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Published at') }}</td><td>{{ $content->published_at?->format('M d, Y H:i') ?? '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Source') }}</td><td>{{ $content->is_user_submitted ? __('User submission') : __('Admin') }} @if ($content->submittedBy) &middot; {{ $content->submittedBy->name }} @endif</td></tr>
                            <tr><td class="fw-semibold">{{ __('Reviewed by') }}</td><td>{{ $content->reviewedBy?->name ?? '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Excerpt') }}</td><td>{{ $content->excerpt ?: '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Body') }}</td><td style="white-space: pre-wrap;">{{ $content->body ?: '-' }}</td></tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Tags') }}</td>
                                <td>
                                    @forelse ($content->tags as $tag)
                                        <span class="badge text-bg-light border">{{ $tag->name }}</span>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Media') }}</td>
                                <td>
                                    @forelse ($content->media as $media)
                                        <div class="mb-1">
                                            <span class="badge text-bg-secondary">{{ $media->pivot->role }}</span>
                                            {{ $media->original_filename }}
                                        </div>
                                    @empty
                                        -
                                    @endforelse
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Quick status') }}</h6></div>
                <div class="card-body d-grid gap-2">
                    @foreach (['draft', 'pending_review', 'published', 'rejected'] as $status)
                        <form action="{{ route('admin.contents.status', $content) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $status }}">
                            <button type="submit" class="btn btn-sm w-100 {{ $content->status === $status ? 'btn-primary' : 'btn-outline-secondary' }}" @if ($content->status === $status) disabled @endif>
                                {{ __('Set :status', ['status' => ucwords(str_replace('_', ' ', $status))]) }}
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>

            <div class="card border-danger">
                <div class="card-header"><h6 class="mb-0 fw-semibold text-danger">{{ __('Danger Zone') }}</h6></div>
                <div class="card-body">
                    <p class="text-muted" style="font-size: 0.875rem;">{{ __('Deleting content removes it permanently along with its media links and tags.') }}</p>
                    <form action="{{ route('admin.contents.destroy', $content) }}" method="POST" onsubmit="return confirm('{{ __('Delete this content permanently?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100"><i class="bi bi-trash me-1"></i>{{ __('Delete Content') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
