@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Media Library') }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ __('Manage images, videos, audio, and documents. Large movies use chunked upload (up to 512MB).') }}</p>
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-upload me-1"></i>{{ __('Upload Files') }}
            </button>
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
                                @if ($item->url)
                                <img src="{{ $item->url }}" alt="{{ $item->alt_text ?: $item->original_filename }}" class="img-fluid rounded mb-2" style="max-height: 80px; object-fit: cover;">
                                @endif
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
                            @if ($item->status === 'uploading')
                                <div class="text-info" style="font-size: 0.65rem;">
                                    <i class="bi bi-arrow-clockwise"></i> {{ $item->uploaded_chunks }}/{{ $item->total_chunks }} chunks
                                </div>
                            @endif
                        </div>
                        <div class="card-footer bg-transparent p-1 text-center">
                            @if ($item->url)
                            <a href="{{ $item->url }}" class="btn btn-outline-info btn-sm" style="font-size: 0.7rem;" target="_blank" rel="noopener"><i class="bi bi-eye"></i></a>
                            @endif
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

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="uploadModalLabel"><i class="bi bi-upload me-2"></i>{{ __('Upload Media Files') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
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
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ __('Select Files') }}</label>
                            <input type="file" class="form-control @error('files') is-invalid @enderror" name="files[]" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.mp4,.webm,.ogv,.mov,.avi,.mkv,.wmv,.3gp,.mp3,.wav,.ogg,.m4a,.aac" required>
                            <small class="text-muted">{{ __('Images max 5MB, documents 25MB, video 100MB, movies up to 512MB (auto chunked). Up to 10 files per upload.') }}</small>
                            <div id="fileList" class="mt-2 small text-muted"></div>
                            @error('files')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            @error('files.*')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Progress Bar (hidden by default) -->
                        <div id="uploadProgress" class="d-none mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-medium">{{ __('Uploading...') }}</span>
                                <button type="button" id="cancelUpload" class="btn btn-sm btn-outline-danger" style="font-size: 0.75rem;">{{ __('Cancel') }}</button>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div id="progressText" class="text-muted small mt-1 text-end"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="uploadBtn">
                            <i class="bi bi-upload me-1"></i>{{ __('Upload') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload modal functionality
    const uploadForm = document.querySelector('#uploadModal form');
    if (!uploadForm) return;

    const fileInput = uploadForm.querySelector('input[type="file"]');
    const uploadBtn = uploadForm.querySelector('button[type="submit"]');
    const progressContainer = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const fileList = document.getElementById('fileList');
    const cancelBtn = document.getElementById('cancelUpload');

    let uploadXhr = null;
    let isChunkedUpload = false;
    let uploadMediaId = null;
    let totalChunks = 0;
    let uploadedChunks = 0;
    let chunkSize = 5 * 1024 * 1024; // 5MB default chunk size
    let currentFile = null;
    let currentChunkIndex = 0;

    // File input change - show file list
    fileInput.addEventListener('change', function() {
        fileList.innerHTML = '';
        Array.from(this.files).forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'd-flex justify-content-between align-items-center py-1 border-bottom small';
            div.innerHTML = `
                <span class="text-truncate me-2" style="max-width: 200px;">${file.name}</span>
                <span class="text-muted">${formatBytes(file.size)}</span>
            `;
            fileList.appendChild(div);
        });
    });

    // Form submit - handle both regular and chunked upload
    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const files = fileInput.files;
        if (files.length === 0) return;

        // Check if any file needs chunked upload (> 100MB)
        let needsChunked = false;
        for (let file of files) {
            if (file.size > 100 * 1024 * 1024) { // > 100MB
                needsChunked = true;
                break;
            }
        }

        if (needsChunked && files.length === 1) {
            // Use chunked upload for single large file
            await uploadChunked(files[0]);
        } else {
            // Regular upload for multiple or smaller files
            await uploadRegular(files);
        }
    });

    async function uploadRegular(files) {
        const formData = new FormData();
        for (let file of files) {
            formData.append('files[]', file);
        }
        formData.append('alt_text', document.getElementById('alt_text')?.value || '');
        
        const durationHours = document.getElementById('duration_hours')?.value || 0;
        const durationMinutes = document.getElementById('duration_minutes')?.value || 0;
        const durationSeconds = document.getElementById('duration_seconds')?.value || 0;
        formData.append('duration_hours', durationHours);
        formData.append('duration_minutes', durationMinutes);
        formData.append('duration_seconds', durationSeconds);
        
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        uploadXhr = new XMLHttpRequest();
        
        uploadXhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                const percent = Math.round((e.loaded / e.total) * 100);
                updateProgress(percent, `${formatBytes(e.loaded)} / ${formatBytes(e.total)}`);
            }
        });

        uploadXhr.addEventListener('load', function() {
            if (uploadXhr.status >= 200 && uploadXhr.status < 300) {
                hideProgress();
                bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
                location.reload();
            } else {
                showError('Upload failed: ' + uploadXhr.responseText);
            }
        });

        uploadXhr.addEventListener('error', function() {
            showError('Upload failed. Please try again.');
        });

        uploadXhr.addEventListener('abort', function() {
            hideProgress();
        });

        uploadXhr.open('POST', '{{ route('admin.media.store') }}', true);
        uploadXhr.send(formData);

        showProgress();
    }

    async function uploadChunked(file) {
        // Initialize chunked upload
        const chunkSize = 5 * 1024 * 1024; // 5MB chunks
        const totalChunks = Math.ceil(file.size / chunkSize);
        
        isChunkedUpload = true;
        totalChunks = totalChunks;
        uploadedChunks = 0;
        currentFile = file;
        currentChunkIndex = 0;

        // Initialize upload session
        const initFormData = new FormData();
        initFormData.append('filename', file.name);
        initFormData.append('total_size', file.size);
        initFormData.append('total_chunks', totalChunks);
        initFormData.append('chunk_size', chunkSize);
        initFormData.append('mime_type', file.type);
        initFormData.append('alt_text', document.getElementById('alt_text')?.value || '');
        initFormData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
            const initResponse = await fetch('{{ route('media.chunk.init') }}', {
                method: 'POST',
                body: initFormData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const initData = await initResponse.json();
            if (!initResponse.ok) throw new Error(initData.message || 'Failed to initialize upload');
            
            uploadMediaId = initData.media_id;
            showProgress();

            // Upload chunks
            for (let i = 0; i < totalChunks; i++) {
                if (uploadXhr && uploadXhr.readyState === XMLHttpRequest.OPENED) {
                    // Check if cancelled
                    if (cancelBtn?.dataset.cancelled === 'true') break;
                }

                currentChunkIndex = i;
                const start = i * chunkSize;
                const end = Math.min(start + chunkSize, file.size);
                const chunk = file.slice(start, end);

                const chunkFormData = new FormData();
                chunkFormData.append('media_id', uploadMediaId);
                chunkFormData.append('chunk_index', i);
                chunkFormData.append('chunk', chunk);
                chunkFormData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                const chunkResponse = await fetch('{{ route('media.chunk.upload') }}', {
                    method: 'POST',
                    body: chunkFormData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const chunkData = await chunkResponse.json();
                if (!chunkResponse.ok) throw new Error(chunkData.message || `Chunk ${i} failed`);

                uploadedChunks = chunkData.uploaded_chunks;
                const progress = chunkData.progress;
                updateProgress(progress, `Chunk ${i + 1} / ${totalChunks} (${formatBytes(i * chunkSize)} / ${formatBytes(file.size)})`);
            }

            // Complete upload
            const durationHours = document.getElementById('duration_hours')?.value || 0;
            const durationMinutes = document.getElementById('duration_minutes')?.value || 0;
            const durationSeconds = document.getElementById('duration_seconds')?.value || 0;

            const completeFormData = new FormData();
            completeFormData.append('media_id', uploadMediaId);
            completeFormData.append('duration_hours', durationHours);
            completeFormData.append('duration_minutes', durationMinutes);
            completeFormData.append('duration_seconds', durationSeconds);
            completeFormData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            const completeResponse = await fetch('{{ route('media.chunk.complete') }}', {
                method: 'POST',
                body: completeFormData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
            });

            const completeData = await completeResponse.json();
            if (!completeResponse.ok) throw new Error(completeData.message || 'Failed to complete upload');

            hideProgress();
            bootstrap.Modal.getInstance(document.getElementById('uploadModal')).hide();
            location.reload();

        } catch (error) {
            showError(error.message);
            // Try to cancel upload
            if (uploadMediaId) {
                await fetch('{{ route('media.chunk.cancel') }}', {
                    method: 'POST',
                    body: new URLSearchParams({ media_id: uploadMediaId, _token: document.querySelector('meta[name="csrf-token"]').content }),
                });
            }
        }
    }

    // Cancel upload
    cancelBtn?.addEventListener('click', function() {
        if (uploadXhr) {
            uploadXhr.abort();
        }
        if (isChunkedUpload && uploadMediaId) {
            this.dataset.cancelled = 'true';
            fetch('{{ route('media.chunk.cancel') }}', {
                method: 'POST',
                body: new URLSearchParams({ media_id: uploadMediaId, _token: document.querySelector('meta[name="csrf-token"]').content }),
            });
        }
        hideProgress();
    });

    function showProgress() {
        progressContainer?.classList.remove('d-none');
        uploadBtn?.setAttribute('disabled', 'disabled');
        fileInput?.setAttribute('disabled', 'disabled');
        updateProgress(0, 'Starting...');
    }

    function hideProgress() {
        progressContainer?.classList.add('d-none');
        uploadBtn?.removeAttribute('disabled');
        fileInput?.removeAttribute('disabled');
        cancelBtn?.dataset.cancelled = 'false';
    }

    function updateProgress(percent, text = '') {
        progressBar?.style.setProperty('width', percent + '%');
        progressBar?.setAttribute('aria-valuenow', percent);
        progressText?.textContent = text || `${Math.round(percent)}%`;
    }

    function showError(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
        alert.innerHTML = `${message} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        uploadForm.insertAdjacentElement('afterbegin', alert);
        hideProgress();
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Reset modal on hide
    document.getElementById('uploadModal')?.addEventListener('hidden.bs.modal', function() {
        uploadForm.reset();
        fileList.innerHTML = '';
        hideProgress();
    });
});
</script>
@endpush