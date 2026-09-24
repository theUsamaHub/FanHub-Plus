@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="h4 mb-0 fw-semibold">{{ __('Edit Media') }}</h2>
            <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card fh-adm-form-card">
                <div class="card-body p-4">
                    <div class="mb-4 text-center">
                        @if ($media->isImage())
                            <img src="{{ $media->url }}" alt="{{ $media->alt_text ?: $media->original_filename }}" class="img-thumbnail" style="max-height: 240px;">
                        @elseif ($media->isVideo())
                            <video src="{{ $media->url }}" controls class="img-thumbnail" style="max-height: 240px;"></video>
                        @elseif ($media->isAudio())
                            <audio src="{{ $media->url }}" controls class="w-100"></audio>
                        @else
                            <i class="bi bi-file-earmark fs-1 text-secondary"></i>
                        @endif
                    </div>

                    <table class="table mb-4">
                        <tbody>
                            <tr>
                                <td class="fw-semibold" style="width: 180px;">{{ __('Filename') }}</td>
                                <td>{{ $media->original_filename }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Type') }}</td>
                                <td>{{ $media->media_type }} ({{ $media->mime_type }})</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Size') }}</td>
                                <td>{{ $media->size_formatted }}</td>
                            </tr>
                            @if ($media->width && $media->height)
                                <tr>
                                    <td class="fw-semibold">{{ __('Dimensions') }}</td>
                                    <td>{{ $media->width }} &times; {{ $media->height }} px</td>
                                </tr>
                            @endif
                            @if ($media->duration !== null)
                                <tr>
                                    <td class="fw-semibold">{{ __('Duration') }}</td>
                                    <td>{{ $media->duration_formatted }} ({{ $media->duration }} {{ __('seconds') }})</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="fw-semibold">{{ __('References') }}</td>
                                <td>{{ $media->referenceCount() }}</td>
                            </tr>
                            <tr>
                                <td class="fw-semibold">{{ __('Uploaded') }}</td>
                                <td>{{ $media->created_at->format('M d, Y H:i') }} @if ($media->uploadedBy) &middot; {{ $media->uploadedBy->name }} @endif</td>
                            </tr>
                        </tbody>
                    </table>

                    <form action="{{ route('admin.media.update', $media) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <x-input-label for="alt_text" :value="__('Alt text')" />
                            <x-text-input id="alt_text" name="alt_text" type="text" class="form-control" :value="old('alt_text', $media->alt_text)" maxlength="255" />
                            <small class="text-muted">{{ __('Descriptive text for accessibility and SEO.') }}</small>
                            <x-input-error :messages="$errors->get('alt_text')" class="mt-1" />
                        </div>

                        @if ($media->isVideo() || $media->isAudio())
                            @php $durationParts = $media->duration_parts; @endphp
                            <div class="mb-3">
                                <x-input-label :value="__('Duration')" />
                                <div class="row g-2">
                                    <div class="col-4">
                                        <input type="number" class="form-control" name="duration_hours" min="0" max="23" value="{{ old('duration_hours', $durationParts['hours']) }}">
                                        <small class="text-muted">{{ __('Hours') }}</small>
                                    </div>
                                    <div class="col-4">
                                        <input type="number" class="form-control" name="duration_minutes" min="0" max="59" value="{{ old('duration_minutes', $durationParts['minutes']) }}">
                                        <small class="text-muted">{{ __('Minutes') }}</small>
                                    </div>
                                    <div class="col-4">
                                        <input type="number" class="form-control" name="duration_seconds" min="0" max="59.99" step="0.01" value="{{ old('duration_seconds', $durationParts['seconds']) }}">
                                        <small class="text-muted">{{ __('Seconds') }}</small>
                                    </div>
                                </div>
                                <small class="text-muted">{{ __('Automatically saved as total seconds.') }}</small>
                                <x-input-error :messages="$errors->get('duration_hours')" class="mt-1" />
                                <x-input-error :messages="$errors->get('duration_minutes')" class="mt-1" />
                                <x-input-error :messages="$errors->get('duration_seconds')" class="mt-1" />
                            </div>
                        @endif

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                            <x-primary-button>{{ __('Save changes') }}</x-primary-button>
                        </div>
                    </form>
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
                        {{ __('Files that are still referenced by categories, content, characters, merchandise, events, or profiles cannot be deleted.') }}
                    </p>
                    <form action="{{ route('admin.media.destroy', $media) }}" method="POST" onsubmit="return confirm('{{ __('Delete this file permanently?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm w-100" @if ($media->isReferenced()) disabled @endif>
                            <i class="bi bi-trash me-1"></i>{{ __('Delete File') }}
                        </button>
                    </form>
                    @if ($media->isReferenced())
                        <p class="text-danger mt-2 mb-0" style="font-size: 0.8rem;">{{ __('This file is in use and cannot be deleted.') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
