@extends('layouts.app')

@section('content')
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Submission Review') }}</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td class="fw-semibold" style="width:180px;">{{ __('Title') }}</td><td>{{ $content->title }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Slug') }}</td><td><code>{{ $content->slug }}</code></td></tr>
                            <tr><td class="fw-semibold">{{ __('Submitter') }}</td><td>{{ $content->submittedBy?->name ?? '-' }} @if ($content->submittedBy) &lt;{{ $content->submittedBy->email }}&gt; @endif</td></tr>
                            <tr><td class="fw-semibold">{{ __('Category') }}</td><td>{{ $content->category?->name ?? '-' }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Type') }}</td><td>{{ $content->type }}</td></tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Status') }}</td>
                                <td>
                                    @php $statusClass = ['draft' => 'secondary', 'pending_review' => 'warning', 'published' => 'success', 'rejected' => 'danger'][$content->status] ?? 'secondary'; @endphp
                                    <span class="badge text-bg-{{ $statusClass }}">{{ ucwords(str_replace('_', ' ', $content->status)) }}</span>
                                </td>
                            </tr>
                            <tr><td class="fw-semibold">{{ __('Submitted') }}</td><td>{{ $content->created_at->format('M d, Y H:i') }}</td></tr>
                            <tr><td class="fw-semibold">{{ __('Release date') }}</td><td>{{ $content->release_date?->format('Y-m-d') ?? '-' }}</td></tr>
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
                            <tr><td class="fw-semibold">{{ __('Reviewed by') }}</td><td>{{ $content->reviewedBy?->name ?? '-' }}</td></tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Note') }}</td>
                                <td class="text-muted">{{ __('Review is approve/reject only. Submitter account and role cannot be changed from this screen.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Moderation') }}</h6></div>
                <div class="card-body d-grid gap-2">
                    @if ($content->status === 'pending_review')
                        <form action="{{ route('admin.submissions.approve', $content) }}" method="POST" onsubmit="return confirm('{{ __('Approve and publish this submission?') }}')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success w-100"><i class="bi bi-check-lg me-1"></i>{{ __('Approve and publish') }}</button>
                        </form>
                        <form action="{{ route('admin.submissions.reject', $content) }}" method="POST" onsubmit="return confirm('{{ __('Reject this submission?') }}')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-danger w-100"><i class="bi bi-x-lg me-1"></i>{{ __('Reject') }}</button>
                        </form>
                    @else
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">{{ __('This submission has already been reviewed.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
