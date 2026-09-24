@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h4 mb-0 fw-semibold">{{ __('Media Library') }}</h2>
                </div>
    </div>

    <!-- Upload Form -->
    <div class="card mb-4 fh-adm-filter">
        <div class="card-header">
            <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Upload Files') }}</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="alt_text" class="form-label">{{ __('Alt text (applied to all uploaded files)') }}</label>
                    <input type="text" class="form-control" name="alt_text" id="alt_text" maxlength="255" value="{{ old('alt_text') }}" placeholder="{{ __('Descriptive text for accessibility') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ __('Duration (video/audio only)') }}</label>
                    <div class="row g-2">
                        <div class="col-4">
                            <input type="number" class="form-control" name="duration_hours" id="duration_hours" min="0" max="23" value="{{ old('duration_hours', 0) }}" placeholder="0">
                            <small class="text-muted">{{ __('Hours') }}</small>
                        </div>
                        <div class="col-4">
                            <input type="number" class="form-control" name="duration_minutes" id="duration_minutes" min="0" max="59" value="{{ old('duration_minutes', 0) }}" placeholder="0">
                            <small class="text-muted">{{ __('Minutes') }}</small>
                        </div>
                        <div class="col-4">
                            <input type="number" class="form-control" name="duration_seconds" id="duration_seconds" min="0" max="59.99" step="0.01" value="{{ old('duration_seconds', 0) }}" placeholder="0">
                            <small class="text-muted">{{ __('Seconds') }}</small>
                        </div>
                    </div>
                    <small class="text-muted">{{ __('Automatically saved as total seconds.') }}</small>
                    @error('duration_hours')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('duration_minutes')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('duration_seconds')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <input type="file" class="form-control @error('files') is-invalid @enderror" name="files[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.mp4,.webm,.ogv,.mov,.mp3,.wav,.ogg,.m4a,.aac" required>
                    <small class="text-muted">{{ __('Images max 5MB, documents 10MB, audio 20MB, video 50MB. Up to 10 files per upload.') }}</small>
                    @error('files')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                    @error('files.*')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-upload me-1"></i>{{ __('Upload') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 fh-adm-filter">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.media.index') }}" class="row g-3">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="search" placeholder="{{ __('Search files...') }}" value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select class="form-select" name="type">
                        <option value="">{{ __('All Types') }}</option>
                        <option value="image" {{ request('type') === 'image' ? 'selected' : '' }}>{{ __('Images') }}</option>
                        <option value="video" {{ request('type') === 'video' ? 'selected' : '' }}>{{ __('Video') }}</option>
                        <option value="audio" {{ request('type') === 'audio' ? 'selected' : '' }}>{{ __('Audio') }}</option>
                        <option value="document" {{ request('type') === 'document' ? 'selected' : '' }}>{{ __('Documents') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-search me-1"></i>{{ __('Filter') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Files Grid -->
    <div class="card fh-adm-form-card">
        <div class="card-body">
            @forelse ($media as $item)
                @if ($loop->first || ($loop->index % 6 === 0))
                    <div class="row g-3 mb-3">
                @endif

                <div class="col-md-2">
                    <div class="card h-100 border">
                        <div class="card-body p-2 text-center">
                            @if ($item->isImage())
                                <img src="{{ $item->url }}" alt="{{ $item->alt_text ?: $item->original_filename }}" class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: cover;">
                            @elseif ($item->isVideo())
                                <i class="bi bi-film text-primary fs-1"></i>
                            @elseif ($item->isAudio())
                                <i class="bi bi-music-note-beamed text-info fs-1"></i>
                            @elseif ($item->mime_type === 'application/pdf')
                                <i class="bi bi-file-earmark-pdf text-danger fs-1"></i>
                            @else
                                <i class="bi bi-file-earmark text-secondary fs-1"></i>
                            @endif
                            <div class="text-truncate" style="font-size: 0.75rem;" title="{{ $item->original_filename }}">{{ $item->original_filename ?: __('Untitled') }}</div>
                            <div class="text-muted" style="font-size: 0.65rem;">{{ $item->size_formatted }} &middot; {{ $item->media_type }}</div>
                            @if ($item->isReferenced())
                                <div class="text-warning" style="font-size: 0.65rem;"><i class="bi bi-link-45deg"></i> {{ __('In use') }}</div>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent p-1 text-center">
                            <a href="{{ $item->url }}" class="btn btn-outline-info btn-sm" style="font-size: 0.7rem;" target="_blank" rel="noopener"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('admin.media.edit', $item) }}" class="btn btn-outline-primary btn-sm" style="font-size: 0.7rem;" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.media.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this file?') }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" style="font-size: 0.7rem;" @if ($item->isReferenced()) disabled title="{{ __('Referenced by other records') }}" @endif><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                </div>

                @if ($loop->last || ($loop->index % 6 === 5))
                    </div>
                @endif
            @empty
                <div class="fh-adm-empty">
                    <i class="bi bi-folder"></i>
                    <p>{{ __('No files uploaded yet.') }}</p>
                </div>
            @endforelse
        </div>

        @if ($media->hasPages())
            <div class="card-footer bg-white">
                {{ $media->links() }}
            </div>
        @endif
    </div>
@endsection
