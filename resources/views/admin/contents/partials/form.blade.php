@php
    $selectedTags = old('tags', $content?->tags->pluck('id')->all() ?? []);
@endphp

<div class="row">
    <div class="col-lg-8">
          <form action="{{ $action }}" method="POST" id="adminContentForm">
            @csrf
            @method($method)

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title"><i class="bi bi-pencil-square me-1"></i>{{ __('Basic Information') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="form-control" :value="old('title', $content?->title)" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="slug" :value="__('Slug (optional)')" />
                        <x-text-input id="slug" name="slug" type="text" class="form-control" :value="old('slug', $content?->slug)" />
                        <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <x-input-label for="category_id" :value="__('Category')" />
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id', $content?->category_id) === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <x-input-label for="type" :value="__('Type')" />
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach (['article', 'video', 'audio', 'image'] as $type)
                                    <option value="{{ $type }}" @selected(old('type', $content?->type ?? 'article') === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-1" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <x-input-label for="release_date" :value="__('Release date')" />
                            <x-text-input id="release_date" name="release_date" type="date" class="form-control" :value="old('release_date', $content?->release_date?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('release_date')" class="mt-1" />
                        </div>
                        <div class="col-md-3 mb-3">
                            <x-input-label for="release_label" :value="__('Release label')" />
                            <x-text-input id="release_label" name="release_label" type="text" class="form-control" :value="old('release_label', $content?->release_label)" placeholder="{{ __('e.g. Premiere') }}" />
                            <x-input-error :messages="$errors->get('release_label')" class="mt-1" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title"><i class="bi bi-fonts me-1"></i>{{ __('Editorial Content') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <x-input-label for="excerpt" :value="__('Excerpt')" />
                        <textarea id="excerpt" name="excerpt" rows="2" maxlength="500" class="form-control @error('excerpt') is-invalid @enderror">{{ old('excerpt', $content?->excerpt) }}</textarea>
                        <x-input-error :messages="$errors->get('excerpt')" class="mt-1" />
                    </div>
                    <div class="mb-0">
                        <x-input-label for="body" :value="__('Body')" />
                        <textarea id="body" name="body" rows="10" class="form-control @error('body') is-invalid @enderror">{{ old('body', $content?->body) }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title"><i class="bi bi-images me-1"></i>{{ __('Media') }}</h6>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <a href="{{ route('admin.media.index') }}" class="btn btn-sm btn-outline-primary fh-adm-open-library" target="_blank" rel="noopener">
                            <i class="bi bi-folder2-open me-1"></i>{{ __('Open Media Library') }}
                        </a>
                        <span class="fh-adm-chip" data-tone="accent" id="fhMediaSelectedCount" data-label="{{ __('selected') }}">0 {{ __('selected') }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-lg-6 mb-0">
                            <div class="fh-adm-media-block">
                                <div class="fh-adm-media-block-head">
                                    <div class="fh-adm-media-block-head-copy">
                                        <span class="fh-adm-media-block-icon" data-tone="images"><i class="bi bi-image"></i></span>
                                        <div class="min-w-0">
                                            <div class="fh-adm-media-block-title">{{ __('Cover Image') }}</div>
                                            <div class="fh-adm-media-block-desc">{{ __('Select one image to be the main cover.') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary fh-adm-view-all" data-fh-media="cover">
                                        <i class="bi bi-arrows-fullscreen me-1"></i>{{ __('View all') }}
                                    </button>
                                </div>
                                <div class="fh-adm-pick-list fh-adm-pick-grid" data-fh-list="cover" data-pick="single">
                                    <label class="fh-adm-pick-item fh-adm-pick-card">
                                        <input type="radio" name="cover_media_id" value="" @checked((int) old('cover_media_id', $selected['cover']) === 0)>
                                        <div class="fh-adm-pick-card-body">
                                            <div class="fh-adm-pick-thumb fh-adm-pick-thumb--none"><i class="bi bi-x-lg"></i><span class="fh-adm-pick-check"><i class="bi bi-check-lg"></i></span></div>
                                            <div class="fh-adm-pick-name">{{ __('None') }}</div>
                                            <div class="fh-adm-pick-meta">{{ __('No cover') }}</div>
                                        </div>
                                    </label>
                                    @foreach ($mediaOptions['images'] as $media)
                                        <label class="fh-adm-pick-item fh-adm-pick-card">
                                            <input type="radio" name="cover_media_id" value="{{ $media->id }}" @checked((int) old('cover_media_id', $selected['cover']) === $media->id)>
                                            <div class="fh-adm-pick-card-body">
                                                <div class="fh-adm-pick-thumb">
                                                    @if ($media->isImage() && $media->url)
                                                        <img src="{{ $media->url }}" alt="{{ $media->alt_text ?: $media->original_filename }}" loading="lazy">
                                                    @else
                                                        <i class="bi bi-image"></i>
                                                    @endif
                                                    <span class="fh-adm-pick-check"><i class="bi bi-check-lg"></i></span>
                                                </div>
                                                <div class="fh-adm-pick-name" title="{{ $media->original_filename }}">{{ $media->original_filename }}</div>
                                                <div class="fh-adm-pick-meta">{{ $media->mime_type ?: $media->size_formatted }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                    @if ($mediaOptions['images']->isEmpty())
                                        <div class="fh-adm-tile-meta">{{ __('No images in the media library yet.') }}</div>
                                    @endif
                                </div>
                                <x-input-error :messages="$errors->get('cover_media_id')" class="mt-1" />
                            </div>
                        </div>

                        <div class="col-lg-6 mb-0">
                            <div class="fh-adm-media-block">
                                <div class="fh-adm-media-block-head">
                                    <div class="fh-adm-media-block-head-copy">
                                        <span class="fh-adm-media-block-icon" data-tone="videos"><i class="bi bi-film"></i></span>
                                        <div class="min-w-0">
                                            <div class="fh-adm-media-block-title">{{ __('Trailer Video') }}</div>
                                            <div class="fh-adm-media-block-desc">{{ __('Select one trailer video (no thumbnail, shown as file list).') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary fh-adm-view-all" data-fh-media="trailer">
                                        <i class="bi bi-arrows-fullscreen me-1"></i>{{ __('View all') }}
                                    </button>
                                </div>
                                <div class="fh-adm-pick-list fh-adm-pick-list--single" data-fh-list="trailer" data-pick="single">
                                    <label class="fh-adm-pick-item">
                                        <input type="radio" name="trailer_media_id" value="" @checked((int) old('trailer_media_id', $selected['trailer']) === 0)>
                                        <div class="fh-adm-pick-item-body">
                                            <div class="fh-adm-pick-icon"><i class="bi bi-x-lg"></i></div>
                                            <div class="min-w-0">
                                                <div class="fh-adm-pick-name">{{ __('None') }}</div>
                                                <div class="fh-adm-pick-meta">{{ __('No trailer') }}</div>
                                            </div>
                                            <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                        </div>
                                    </label>
                                    @foreach ($mediaOptions['videos'] as $media)
                                        @php
                                            $videoExt = strtoupper(pathinfo((string) $media->original_filename, PATHINFO_EXTENSION));
                                            $videoMeta = collect([$videoExt, $media->width && $media->height ? $media->width.'×'.$media->height : null, $media->duration_formatted])->filter()->implode(' · ');
                                        @endphp
                                        <label class="fh-adm-pick-item">
                                            <input type="radio" name="trailer_media_id" value="{{ $media->id }}" @checked((int) old('trailer_media_id', $selected['trailer']) === $media->id)>
                                            <div class="fh-adm-pick-item-body">
                                                <div class="fh-adm-pick-icon"><i class="bi bi-film"></i></div>
                                                <div class="min-w-0">
                                                    <div class="fh-adm-pick-name" title="{{ $media->original_filename }}">{{ $media->original_filename }}</div>
                                                    <div class="fh-adm-pick-meta">{{ $videoMeta }}</div>
                                                </div>
                                                <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                                @if ($media->url)
                                                    <a class="fh-adm-pick-action" href="{{ $media->url }}" target="_blank" rel="noopener" title="{{ __('Preview') }}">
                                                        <i class="bi bi-play-circle"></i><span>{{ __('Preview') }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                    @if ($mediaOptions['videos']->isEmpty())
                                        <div class="fh-adm-tile-meta">{{ __('No videos in the media library yet.') }}</div>
                                    @endif
                                </div>
                                <x-input-error :messages="$errors->get('trailer_media_id')" class="mt-1" />
                            </div>
                        </div>

                        <div class="col-lg-6 mb-0">
                            <div class="fh-adm-media-block">
                                <div class="fh-adm-media-block-head">
                                    <div class="fh-adm-media-block-head-copy">
                                        <span class="fh-adm-media-block-icon" data-tone="audio"><i class="bi bi-music-note-beamed"></i></span>
                                        <div class="min-w-0">
                                            <div class="fh-adm-media-block-title">{{ __('Audio Clip') }}</div>
                                            <div class="fh-adm-media-block-desc">{{ __('Select one audio clip (no thumbnail, shown as file list).') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary fh-adm-view-all" data-fh-media="audio">
                                        <i class="bi bi-arrows-fullscreen me-1"></i>{{ __('View all') }}
                                    </button>
                                </div>
                                <div class="fh-adm-pick-list fh-adm-pick-list--single" data-fh-list="audio" data-pick="single">
                                    <label class="fh-adm-pick-item">
                                        <input type="radio" name="audio_clip_media_id" value="" @checked((int) old('audio_clip_media_id', $selected['audio_clip']) === 0)>
                                        <div class="fh-adm-pick-item-body">
                                            <div class="fh-adm-pick-icon"><i class="bi bi-x-lg"></i></div>
                                            <div class="min-w-0">
                                                <div class="fh-adm-pick-name">{{ __('None') }}</div>
                                                <div class="fh-adm-pick-meta">{{ __('No audio') }}</div>
                                            </div>
                                            <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                        </div>
                                    </label>
                                    @foreach ($mediaOptions['audio'] as $media)
                                        @php
                                            $audioExt = strtoupper(pathinfo((string) $media->original_filename, PATHINFO_EXTENSION));
                                            $audioMeta = collect([$audioExt, $media->duration_formatted, $media->size_formatted])->filter()->implode(' · ');
                                        @endphp
                                        <label class="fh-adm-pick-item">
                                            <input type="radio" name="audio_clip_media_id" value="{{ $media->id }}" @checked((int) old('audio_clip_media_id', $selected['audio_clip']) === $media->id)>
                                            <div class="fh-adm-pick-item-body">
                                                <div class="fh-adm-pick-icon"><i class="bi bi-music-note-beamed"></i></div>
                                                <div class="min-w-0">
                                                    <div class="fh-adm-pick-name" title="{{ $media->original_filename }}">{{ $media->original_filename }}</div>
                                                    <div class="fh-adm-pick-meta">{{ $audioMeta }}</div>
                                                </div>
                                                <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                                @if ($media->url)
                                                    <button type="button" class="fh-adm-pick-action fh-adm-pick-action--play" data-fh-play="{{ $media->url }}" title="{{ __('Play') }}">
                                                        <i class="bi bi-play-circle"></i><span>{{ __('Preview') }}</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                    @if ($mediaOptions['audio']->isEmpty())
                                        <div class="fh-adm-tile-meta">{{ __('No audio in the media library yet.') }}</div>
                                    @endif
                                </div>
                                <x-input-error :messages="$errors->get('audio_clip_media_id')" class="mt-1" />
                            </div>
                        </div>

                        <div class="col-lg-6 mb-0">
                            <div class="fh-adm-media-block">
                                <div class="fh-adm-media-block-head">
                                    <div class="fh-adm-media-block-head-copy">
                                        <span class="fh-adm-media-block-icon" data-tone="gallery"><i class="bi bi-images"></i></span>
                                        <div class="min-w-0">
                                            <div class="fh-adm-media-block-title">{{ __('Gallery Images (Multiple)') }}</div>
                                            <div class="fh-adm-media-block-desc">{{ __('Select multiple images to include in the gallery.') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary fh-adm-view-all" data-fh-media="gallery">
                                        <i class="bi bi-arrows-fullscreen me-1"></i>{{ __('View all') }}
                                    </button>
                                </div>
                                <div class="fh-adm-pick-list fh-adm-pick-grid" data-fh-list="gallery" data-pick="multi">
                                    @forelse ($mediaOptions['images'] as $media)
                                        <label class="fh-adm-pick-item fh-adm-pick-card">
                                            <input type="checkbox" name="gallery_media_ids[]" value="{{ $media->id }}" @checked(in_array($media->id, old('gallery_media_ids', $selected['gallery'])))>
                                            <div class="fh-adm-pick-card-body">
                                                <div class="fh-adm-pick-thumb">
                                                    @if ($media->isImage() && $media->url)
                                                        <img src="{{ $media->url }}" alt="{{ $media->alt_text ?: $media->original_filename }}" loading="lazy">
                                                    @else
                                                        <i class="bi bi-image"></i>
                                                    @endif
                                                    <span class="fh-adm-pick-check"><i class="bi bi-check-lg"></i></span>
                                                </div>
                                                <div class="fh-adm-pick-name" title="{{ $media->original_filename }}">{{ $media->original_filename }}</div>
                                                <div class="fh-adm-pick-meta">{{ $media->mime_type ?: $media->size_formatted }}</div>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="fh-adm-tile-meta">{{ __('No images in the media library yet.') }}</div>
                                    @endforelse
                                </div>
                                <x-input-error :messages="$errors->get('gallery_media_ids')" class="mt-1" />
                            </div>
                        </div>

                        <div class="col-lg-6 mb-0">
                            <div class="fh-adm-media-block">
                                <div class="fh-adm-media-block-head">
                                    <div class="fh-adm-media-block-head-copy">
                                        <span class="fh-adm-media-block-icon" data-tone="documents"><i class="bi bi-paperclip"></i></span>
                                        <div class="min-w-0">
                                            <div class="fh-adm-media-block-title">{{ __('Attachments / Documents') }}</div>
                                            <div class="fh-adm-media-block-desc">{{ __('Select files to attach (optional).') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary fh-adm-view-all" data-fh-media="attachment">
                                        <i class="bi bi-arrows-fullscreen me-1"></i>{{ __('View all') }}
                                    </button>
                                </div>
                                <div class="fh-adm-pick-list" data-fh-list="attachment" data-pick="multi">
                                    @forelse ($mediaOptions['documents'] as $media)
                                        @php
                                            $docExt = strtoupper(pathinfo((string) $media->original_filename, PATHINFO_EXTENSION));
                                            $docMeta = collect([$docExt, $media->size_formatted])->filter()->implode(' · ');
                                        @endphp
                                        <label class="fh-adm-pick-item">
                                            <input type="checkbox" name="attachment_media_ids[]" value="{{ $media->id }}" @checked(in_array($media->id, old('attachment_media_ids', $selected['attachment'])))>
                                            <div class="fh-adm-pick-item-body">
                                                <div class="fh-adm-pick-icon"><i class="bi bi-file-earmark-text"></i></div>
                                                <div class="min-w-0">
                                                    <div class="fh-adm-pick-name" title="{{ $media->original_filename }}">{{ $media->original_filename }}</div>
                                                    <div class="fh-adm-pick-meta">{{ $docMeta }}</div>
                                                </div>
                                                <span class="fh-adm-pick-state">{{ __('Attach') }}</span>
                                                @if ($media->hasValidPath())
                                                    <a class="fh-adm-pick-action" href="{{ route('admin.media.download', $media) }}" title="{{ __('Download') }}">
                                                        <i class="bi bi-download"></i><span>{{ __('Download') }}</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </label>
                                    @empty
                                        <div class="fh-adm-tile-meta">{{ __('No documents in the media library yet.') }}</div>
                                    @endforelse
                                </div>
                                <x-input-error :messages="$errors->get('attachment_media_ids')" class="mt-1" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title"><i class="bi bi-tags me-1"></i>{{ __('Discovery') }}</h6></div>
                <div class="card-body">
                    <x-input-label :value="__('Tags')" />
                    <div class="fh-adm-tag-chips">
                        @foreach ($tags as $tag)
                            <label class="fh-adm-tag-chip">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}" @checked(in_array($tag->id, $selectedTags))>
                                <span>{{ $tag->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('tags')" class="mt-2" />
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title"><i class="bi bi-send me-1"></i>{{ __('Publishing') }}</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-input-label for="status" :value="__('Status')" />
                            <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach (['draft', 'pending_review', 'published', 'rejected'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $content?->status ?? 'published') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label for="published_at" :value="__('Published at').' ('.config('publishing.timezone').')'" />
                            <x-text-input id="published_at" name="published_at" type="datetime-local" class="form-control" :value="old('published_at', $content?->published_at?->copy()->setTimezone(config('publishing.timezone'))->format('Y-m-d\TH:i'))" />
                            <p class="form-text">Leave blank to publish immediately. A future time schedules visibility on the fandom page.</p>
                            <x-input-error :messages="$errors->get('published_at')" class="mt-1" />
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured" @checked(old('is_featured', $content?->is_featured))>
                                <label class="form-check-label" for="is_featured">{{ __('Featured content') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.contents.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                <x-primary-button>{{ $content ? __('Update Content') : __('Create Content') }}</x-primary-button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4 fh-adm-detail-card fh-adm-guide">
            <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title"><i class="bi bi-lightbulb me-1"></i>{{ __('Media Selection Guide') }}</h6></div>
            <div class="card-body">
                <p class="fh-adm-guide-lead">{{ __('Use the right media pattern for a cleaner, faster experience.') }}</p>

                <div class="fh-adm-guide-item" data-tone="images">
                    <div class="fh-adm-guide-head">
                        <span class="fh-adm-guide-icon"><i class="bi bi-image"></i></span>
                        <strong>{{ __('Images (Cover & Gallery)') }}</strong>
                    </div>
                    <ul>
                        <li>{{ __('Show visual thumbnails') }}</li>
                        <li>{{ __('Helps you quickly identify files') }}</li>
                        <li>{{ __('Best for images (JPG, PNG, WebP)') }}</li>
                    </ul>
                </div>

                <div class="fh-adm-guide-item" data-tone="videos">
                    <div class="fh-adm-guide-head">
                        <span class="fh-adm-guide-icon"><i class="bi bi-film"></i></span>
                        <strong>{{ __('Videos (Trailer)') }}</strong>
                    </div>
                    <ul>
                        <li>{{ __('Use compact file rows') }}</li>
                        <li>{{ __('Show filename + metadata') }}</li>
                        <li>{{ __('Include a preview button') }}</li>
                        <li>{{ __('No large thumbnails to keep it light') }}</li>
                    </ul>
                </div>

                <div class="fh-adm-guide-item" data-tone="audio">
                    <div class="fh-adm-guide-head">
                        <span class="fh-adm-guide-icon"><i class="bi bi-music-note-beamed"></i></span>
                        <strong>{{ __('Audio (BGM / OST)') }}</strong>
                    </div>
                    <ul>
                        <li>{{ __('Use compact file rows') }}</li>
                        <li>{{ __('Show filename + duration') }}</li>
                        <li>{{ __('Include a preview button') }}</li>
                        <li>{{ __('Keeps the UI clean and fast') }}</li>
                    </ul>
                </div>

                <div class="fh-adm-guide-item" data-tone="documents">
                    <div class="fh-adm-guide-head">
                        <span class="fh-adm-guide-icon"><i class="bi bi-file-earmark-text"></i></span>
                        <strong>{{ __('Documents (Attachments)') }}</strong>
                    </div>
                    <ul>
                        <li>{{ __('Use compact file rows') }}</li>
                        <li>{{ __('Show filename + file size') }}</li>
                        <li>{{ __('Provide download/preview action') }}</li>
                        <li>{{ __('Avoid large thumbnails') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card fh-adm-detail-card">
            <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title"><i class="bi bi-journal-text me-1"></i>{{ __('Notes') }}</h6></div>
            <div class="card-body">
                <ul class="mb-0" style="font-size: 0.875rem;">
                    <li class="mb-2">{{ __('Choose the fandom category and publish to show this story on its public fandom page. Admin content defaults to published; a future publishing date schedules the story.') }}</li>
                    <li class="mb-2">{{ __('The cover, excerpt, body, gallery and media appear on the public story page. Featured content receives an Editor\'s Pick badge.') }}</li>
                    <li class="mb-2">{{ __('Slug is auto-generated from the title when left blank.') }}</li>
                    <li class="mb-2">{{ __('Use View all to browse every file of that type, filter it by category, then press Use Selected.') }}</li>
                    <li class="mb-2">{{ __('Setting status to published stamps published_at when empty.') }}</li>
                    <li class="mb-0">{{ __('User submission metadata is system-controlled and not editable here.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('modals')
<!-- Media picker modal (View all): kept outside .fh-adm-main-inner because that node has a persistent transform. -->
<div class="fh-adm-media-modal" id="fhMediaModal" role="dialog" aria-modal="true" aria-labelledby="fhMediaModalTitle">
    <div class="fh-adm-media-modal__backdrop" data-fh-media-close></div>
    <div class="fh-adm-media-modal__panel">
        <div class="fh-adm-media-modal__head">
            <h5 class="fh-adm-media-modal__title" id="fhMediaModalTitle">{{ __('Select Media') }}</h5>
            <button type="button" class="fh-adm-media-modal__close" data-fh-media-close aria-label="{{ __('Close') }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="fh-adm-media-modal__body">
            <div class="fh-adm-media-tabs" id="fhMediaTabs" role="tablist" aria-label="{{ __('Filter by category') }}"></div>
            <div class="fh-adm-media-search">
                <i class="bi bi-search"></i>
                <input type="search" class="form-control" id="fhMediaSearch" placeholder="{{ __('Search media...') }}" autocomplete="off">
            </div>
            <div class="fh-adm-media-results" id="fhMediaResults" role="listbox"></div>
        </div>
        <div class="fh-adm-media-modal__foot">
            <span class="fh-adm-media-modal-count" id="fhMediaModalCount"></span>
            <button type="button" class="btn btn-outline-secondary" data-fh-media-close>{{ __('Cancel') }}</button>
            <button type="button" class="btn btn-primary" id="fhMediaUseBtn">{{ __('Use Selected') }}</button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('adminContentForm');
    var modalEl = document.getElementById('fhMediaModal');
    if (!form || !modalEl) return;

    var PAYLOAD = @json($mediaPayload);
    var CATEGORIES = @json($categories->map(fn ($c) => ['id' => (int) $c->id, 'name' => $c->name])->values());
    var L = {
        selected: @json(__('selected')),
        noResults: @json(__('No media matches your search.')),
        preview: @json(__('Preview')),
        download: @json(__('Download')),
        play: @json(__('Play')),
        pause: @json(__('Pause')),
        untitled: @json(__('Untitled'))
    };

    var SECTIONS = {
        cover: { group: 'images', mode: 'single', input: 'cover_media_id', list: 'cover', layout: 'grid', action: null,
            title: @json(__('Select Cover Image')), search: @json(__('Search images...')), all: @json(__('All Images')),
            uncategorized: @json(__('Uncategorized')) },
        gallery: { group: 'images', mode: 'multi', input: 'gallery_media_ids[]', list: 'gallery', layout: 'grid', action: null,
            title: @json(__('Select Gallery Images')), search: @json(__('Search images...')), all: @json(__('All Images')),
            uncategorized: @json(__('Uncategorized')) },
        trailer: { group: 'videos', mode: 'single', input: 'trailer_media_id', list: 'trailer', layout: 'list', action: 'preview',
            title: @json(__('Select Trailer Video')), search: @json(__('Search videos...')), all: @json(__('All Videos')),
            uncategorized: @json(__('Uncategorized')) },
        audio: { group: 'audio', mode: 'single', input: 'audio_clip_media_id', list: 'audio', layout: 'list', action: 'play',
            title: @json(__('Select Audio Clip')), search: @json(__('Search audio...')), all: @json(__('All Audio')),
            uncategorized: @json(__('Uncategorized')) },
        attachment: { group: 'documents', mode: 'multi', input: 'attachment_media_ids[]', list: 'attachment', layout: 'list', action: 'download',
            title: @json(__('Select Document')), search: @json(__('Search documents...')), all: @json(__('All Documents')),
            uncategorized: @json(__('Uncategorized')) }
    };

    var currentKey = null;
    var activeCat = 'all';
    var searchText = '';
    var draft = [];
    var audioEl = null;
    var playingUrl = null;

    var els = {
        title: document.getElementById('fhMediaModalTitle'),
        tabs: document.getElementById('fhMediaTabs'),
        search: document.getElementById('fhMediaSearch'),
        results: document.getElementById('fhMediaResults'),
        count: document.getElementById('fhMediaModalCount'),
        use: document.getElementById('fhMediaUseBtn'),
        chip: document.getElementById('fhMediaSelectedCount')
    };

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function targetOf(event, selector) {
        return event.target && event.target.closest ? event.target.closest(selector) : null;
    }

    function inputsFor(cfg) {
        return Array.prototype.slice.call(form.querySelectorAll('[name="' + cfg.input + '"]'));
    }

    function readSelection(cfg) {
        return inputsFor(cfg)
            .filter(function (input) { return input.checked && input.value !== ''; })
            .map(function (input) { return Number(input.value); });
    }

    function findItem(group, id) {
        var items = PAYLOAD[group] || [];
        for (var i = 0; i < items.length; i++) if (items[i].id === id) return items[i];
        return null;
    }

    function ensureInline(cfg, id) {
        var exists = inputsFor(cfg).some(function (input) { return Number(input.value) === id; });
        if (exists) return;
        var item = findItem(cfg.group, id);
        var list = form.querySelector('[data-fh-list="' + cfg.list + '"]');
        if (!item || !list) return;
        var type = cfg.mode === 'single' ? 'radio' : 'checkbox';

        if (list.classList.contains('fh-adm-pick-grid')) {
            var thumb = item.url ? '<img src="' + esc(item.url) + '" alt="" loading="lazy">' : '<i class="bi bi-image"></i>';
            list.insertAdjacentHTML('beforeend',
                '<label class="fh-adm-pick-item fh-adm-pick-card"><input type="' + type + '" name="' + esc(cfg.input) + '" value="' + item.id + '" checked>'
                + '<div class="fh-adm-pick-card-body"><div class="fh-adm-pick-thumb">' + thumb
                + '<span class="fh-adm-pick-check"><i class="bi bi-check-lg"></i></span></div>'
                + '<div class="fh-adm-pick-name" title="' + esc(item.name || '') + '">' + esc(item.name || L.untitled) + '</div>'
                + '<div class="fh-adm-pick-meta">' + esc(item.mime || item.size || '') + '</div></div></label>');
            return;
        }

        var meta = [item.ext, item.size].filter(Boolean).join(' · ');
        list.insertAdjacentHTML('beforeend',
            '<label class="fh-adm-pick-item"><input type="' + type + '" name="' + esc(cfg.input) + '" value="' + item.id + '" checked>'
            + '<div class="fh-adm-pick-item-body"><div class="fh-adm-pick-icon"><i class="bi bi-file-earmark"></i></div>'
            + '<div class="min-w-0"><div class="fh-adm-pick-name">' + esc(item.name || L.untitled) + '</div>'
            + '<div class="fh-adm-pick-meta">' + esc(meta) + '</div></div>'
            + '<span class="fh-adm-pick-state">' + esc(L.selected) + '</span></div></label>'
        );
    }

    function writeSelection(cfg, ids) {
        inputsFor(cfg).forEach(function (input) {
            if (cfg.mode === 'single') {
                input.checked = ids.length ? Number(input.value) === ids[0] : input.value === '';
            } else {
                input.checked = ids.indexOf(Number(input.value)) !== -1;
            }
        });
        ids.forEach(function (id) { ensureInline(cfg, id); });
    }

    function updateFormCount() {
        if (!els.chip) return;
        var total = 0;
        ['cover_media_id', 'trailer_media_id', 'audio_clip_media_id'].forEach(function (name) {
            var el = form.querySelector('[name="' + name + '"]:checked');
            if (el && el.value !== '') total += 1;
        });
        ['gallery_media_ids[]', 'attachment_media_ids[]'].forEach(function (name) {
            total += form.querySelectorAll('[name="' + name + '"]:checked').length;
        });
        els.chip.textContent = total + ' ' + (els.chip.getAttribute('data-label') || L.selected);
    }

    function updateModalCount() {
        if (!els.count) return;
        els.count.textContent = draft.length + ' ' + L.selected;
    }

    function tabsFor(cfg) {
        var items = PAYLOAD[cfg.group] || [];
        var counts = {};
        items.forEach(function (item) {
            var key = item.category_id ? String(item.category_id) : 'none';
            counts[key] = (counts[key] || 0) + 1;
        });

        var tabs = [{ id: 'all', label: cfg.all, count: items.length }];
        var known = {};
        CATEGORIES.forEach(function (category) {
            var id = String(category.id);
            known[id] = true;
            tabs.push({ id: id, label: category.name, count: counts[id] || 0 });
        });

        // Categories used by media but missing from the content category list.
        Object.keys(counts).forEach(function (key) {
            if (key === 'none' || known[key]) return;
            var name = '';
            items.some(function (item) {
                if (String(item.category_id) !== key) return false;
                name = item.category || '';
                return true;
            });
            tabs.push({ id: key, label: name || ('#' + key), count: counts[key] });
        });

        if (counts.none) tabs.push({ id: 'none', label: cfg.uncategorized, count: counts.none });

        return tabs;
    }

    function renderTabs(cfg) {
        var tabs = tabsFor(cfg);
        els.tabs.style.display = tabs.length > 1 ? '' : 'none';
        els.tabs.innerHTML = tabs.map(function (tab) {
            var active = String(activeCat) === String(tab.id);
            return '<button type="button" class="fh-adm-media-tab' + (active ? ' is-active' : '') + '" role="tab"'
                + ' data-cat="' + esc(tab.id) + '" aria-selected="' + (active ? 'true' : 'false') + '">'
                + '<span>' + esc(tab.label) + '</span>'
                + '<span class="fh-adm-media-tab-count">' + tab.count + '</span></button>';
        }).join('');
    }

    function filteredItems(cfg) {
        var query = searchText.trim().toLowerCase();
        return (PAYLOAD[cfg.group] || []).filter(function (item) {
            if (activeCat === 'all') {
                // keep everything
            } else if (activeCat === 'none') {
                if (item.category_id) return false;
            } else if (String(item.category_id) !== String(activeCat)) {
                return false;
            }
            if (!query) return true;
            return [item.name, item.ext, item.category, item.mime].some(function (field) {
                return field && String(field).toLowerCase().indexOf(query) !== -1;
            });
        });
    }

    function metaFor(item) {
        return [item.ext, item.dimensions, item.duration, item.size].filter(Boolean).join(' \u00b7 ');
    }

    function iconFor(cfg, item) {
        if (cfg.group === 'images') return 'bi-image';
        if (cfg.group === 'videos') return 'bi-film';
        if (cfg.group === 'audio') return 'bi-music-note-beamed';
        return (item.mime === 'application/pdf' || item.ext === 'PDF') ? 'bi-file-earmark-pdf' : 'bi-file-earmark-text';
    }

    function categoryTag(item) {
        return item.category ? '<span class="fh-adm-media-cat">' + esc(item.category) + '</span>' : '';
    }

    function actionFor(cfg, item) {
        if (cfg.action === 'preview' && item.url) {
            return '<a class="fh-adm-media-act" href="' + esc(item.url) + '" target="_blank" rel="noopener" title="' + esc(L.preview) + '" data-stop><i class="bi bi-play-circle"></i></a>';
        }
        if (cfg.action === 'download' && item.download) {
            return '<a class="fh-adm-media-act" href="' + esc(item.download) + '" title="' + esc(L.download) + '" data-stop><i class="bi bi-download"></i></a>';
        }
        if (cfg.action === 'play' && item.url) {
            return '<button type="button" class="fh-adm-media-act" data-play="' + esc(item.url) + '" title="' + esc(L.play) + '" data-stop><i class="bi bi-play-fill"></i></button>';
        }
        return '';
    }

    function renderResults(cfg) {
        var items = filteredItems(cfg);
        els.results.className = 'fh-adm-media-results ' + (cfg.layout === 'grid'
            ? 'fh-adm-media-results--grid'
            : 'fh-adm-media-results--list') + (cfg.mode === 'multi' ? ' is-multi' : ' is-single');

        if (!items.length) {
            els.results.innerHTML = '<div class="fh-adm-media-empty"><i class="bi bi-folder2"></i><p>' + esc(L.noResults) + '</p></div>';
            updateModalCount();
            return;
        }

        if (cfg.layout === 'grid') {
            els.results.innerHTML = items.map(function (item) {
                var on = draft.indexOf(item.id) !== -1;
                var thumb = item.url
                    ? '<img src="' + esc(item.url) + '" alt="" loading="lazy">'
                    : '<i class="bi bi-image"></i>';
                return '<div class="fh-adm-media-card' + (on ? ' is-selected' : '') + '" data-id="' + item.id + '" role="option" tabindex="0"'
                    + ' aria-selected="' + (on ? 'true' : 'false') + '" title="' + esc(item.name) + '">'
                    + '<div class="fh-adm-media-thumb">' + thumb + '<span class="fh-adm-media-check"><i class="bi bi-check-lg"></i></span></div>'
                    + '<div class="fh-adm-media-name">' + esc(item.name || L.untitled) + '</div>'
                    + '<div class="fh-adm-media-meta">' + esc(item.size || '') + categoryTag(item) + '</div>'
                    + '</div>';
            }).join('');
        } else {
            els.results.innerHTML = items.map(function (item) {
                var on = draft.indexOf(item.id) !== -1;
                return '<div class="fh-adm-media-row' + (on ? ' is-selected' : '') + '" data-id="' + item.id + '" role="option" tabindex="0"'
                    + ' aria-selected="' + (on ? 'true' : 'false') + '">'
                    + '<span class="fh-adm-media-mark"><span></span></span>'
                    + '<span class="fh-adm-media-icon"><i class="' + iconFor(cfg, item) + '"></i></span>'
                    + '<span class="fh-adm-media-info">'
                    + '<span class="fh-adm-media-name" title="' + esc(item.name) + '">' + esc(item.name || L.untitled) + '</span>'
                    + '<span class="fh-adm-media-meta">' + esc(metaFor(item)) + categoryTag(item) + '</span>'
                    + '</span>' + actionFor(cfg, item) + '</div>';
            }).join('');
        }

        updateModalCount();
    }

    function applyVisuals() {
        Array.prototype.forEach.call(els.results.querySelectorAll('[data-id]'), function (node) {
            var on = draft.indexOf(Number(node.getAttribute('data-id'))) !== -1;
            node.classList.toggle('is-selected', on);
            node.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        updateModalCount();
    }

    function pick(id) {
        var cfg = SECTIONS[currentKey];
        if (!cfg) return;
        if (cfg.mode === 'single') {
            draft = [id];
        } else {
            var index = draft.indexOf(id);
            if (index === -1) draft.push(id); else draft.splice(index, 1);
        }
        applyVisuals();
    }

    function prepare(key) {
        var cfg = SECTIONS[key];
        if (!cfg) return;
        currentKey = key;
        activeCat = 'all';
        searchText = '';
        draft = readSelection(cfg);
        els.title.textContent = cfg.title;
        els.search.value = '';
        els.search.placeholder = cfg.search;
        renderTabs(cfg);
        renderResults(cfg);
    }

    function refreshPlayIcons() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-fh-play], [data-play]'), function (btn) {
            var url = btn.getAttribute('data-fh-play') || btn.getAttribute('data-play');
            var on = !!audioEl && !audioEl.paused && playingUrl === url;
            var isMediaAct = btn.classList.contains('fh-adm-media-act');
            var base = isMediaAct ? 'bi-play-fill' : 'bi-play-circle';
            var onIcon = isMediaAct ? 'bi-pause-fill' : 'bi-pause-circle';
            var icon = btn.querySelector('i');
            if (icon) icon.className = on ? onIcon : base;
            btn.setAttribute('title', on ? L.pause : L.play);
        });
    }

    function togglePlay(url, btn) {
        if (!audioEl) {
            audioEl = new Audio();
            audioEl.addEventListener('play', refreshPlayIcons);
            audioEl.addEventListener('pause', refreshPlayIcons);
            audioEl.addEventListener('ended', function () { playingUrl = null; refreshPlayIcons(); });
        }
        if (playingUrl === url && !audioEl.paused) { audioEl.pause(); return; }
        if (playingUrl !== url) { audioEl.src = url; }
        playingUrl = url;
        var promise = audioEl.play();
        if (promise && promise.catch) promise.catch(function () {});
        if (btn) refreshPlayIcons();
    }

    function openModal() {
        modalEl.classList.add('is-open');
        document.body.classList.add('fh-adm-modal-open');
        if (els.search) els.search.focus();
    }

    function closeModal() {
        modalEl.classList.remove('is-open');
        document.body.classList.remove('fh-adm-modal-open');
        if (audioEl && !audioEl.paused) audioEl.pause();
        draft = [];
        currentKey = null;
    }

    // Prepare + open on the trigger itself (no dependency on Bootstrap data-api).
    Array.prototype.forEach.call(document.querySelectorAll('[data-fh-media]'), function (trigger) {
        trigger.addEventListener('click', function () {
            prepare(trigger.getAttribute('data-fh-media'));
            openModal();
        });
    });

    modalEl.addEventListener('click', function (event) {
        if (targetOf(event, '[data-fh-media-close]')) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape' || !modalEl.classList.contains('is-open')) return;
        closeModal();
    });

    els.tabs.addEventListener('click', function (event) {
        var tab = targetOf(event, '[data-cat]');
        if (!tab || !currentKey) return;
        activeCat = tab.getAttribute('data-cat');
        renderTabs(SECTIONS[currentKey]);
        renderResults(SECTIONS[currentKey]);
    });

    els.search.addEventListener('input', function () {
        searchText = this.value;
        if (currentKey) renderResults(SECTIONS[currentKey]);
    });

    els.results.addEventListener('click', function (event) {
        var action = targetOf(event, '[data-stop]');
        if (action) {
            event.stopPropagation();
            var playUrl = action.getAttribute('data-play');
            if (playUrl) togglePlay(playUrl, action);
            return;
        }
        var node = targetOf(event, '[data-id]');
        if (!node) return;
        pick(Number(node.getAttribute('data-id')));
    });

    els.results.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        var node = targetOf(event, '[data-id]');
        if (!node) return;
        event.preventDefault();
        pick(Number(node.getAttribute('data-id')));
    });

    els.use.addEventListener('click', function () {
        if (!currentKey) return;
        writeSelection(SECTIONS[currentKey], draft);
        updateFormCount();
        closeModal();
    });

    form.addEventListener('click', function (event) {
        var action = targetOf(event, '.fh-adm-pick-action');
        if (!action) return;
        event.stopPropagation();
        var url = action.getAttribute('data-fh-play');
        if (url) { event.preventDefault(); togglePlay(url, action); }
    });

    form.addEventListener('change', function (event) {
        if (!event.target || !event.target.name) return;
        if (['cover_media_id', 'trailer_media_id', 'audio_clip_media_id', 'gallery_media_ids[]', 'attachment_media_ids[]'].indexOf(event.target.name) !== -1) {
            updateFormCount();
        }
    });

    updateFormCount();
});
</script>
@endpush
