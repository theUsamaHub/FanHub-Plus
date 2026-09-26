@php
    $selectedTags = old('tags', $content?->tags->pluck('id')->all() ?? []);
@endphp

<div class="row">
    <div class="col-lg-8">
        <form action="{{ $action }}" method="POST">
            @csrf
            @method($method)

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Basic') }}</h6></div>
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
                        <div class="col-md-6 mb-3">
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
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <x-input-label for="type" :value="__('Type')" />
                            <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                @foreach (['article', 'video', 'audio', 'image'] as $type)
                                    <option value="{{ $type }}" @selected(old('type', $content?->type ?? 'article') === $type)>{{ ucfirst($type) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-1" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Editorial') }}</h6></div>
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
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Media') }}</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-input-label :value="__('Cover image')" />
                            <div class="fh-adm-pick-list fh-adm-pick-list--single">
                                <label class="fh-adm-pick-item">
                                    <input type="radio" name="cover_media_id" value="" @checked((int) old('cover_media_id', $selected['cover']) === 0)>
                                    <div class="fh-adm-pick-item-body">
                                        <div class="fh-adm-pick-icon"><i class="bi bi-x-lg"></i></div>
                                        <div class="min-w-0">
                                            <div class="fh-adm-pick-name">{{ __('None') }}</div>
                                            <div class="fh-adm-pick-meta">{{ __('No cover image') }}</div>
                                        </div>
                                        <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                    </div>
                                </label>
                                @foreach ($mediaOptions['images'] as $media)
                                    <label class="fh-adm-pick-item">
                                        <input type="radio" name="cover_media_id" value="{{ $media->id }}" @checked((int) old('cover_media_id', $selected['cover']) === $media->id)>
                                        <div class="fh-adm-pick-item-body">
                                            <div class="fh-adm-pick-icon"><i class="bi bi-image"></i></div>
                                            <div class="min-w-0">
                                                <div class="fh-adm-pick-name">{{ $media->original_filename }}</div>
                                                <div class="fh-adm-pick-meta">{{ $media->mime_type }}</div>
                                            </div>
                                            <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('cover_media_id')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label :value="__('Trailer video')" />
                            <div class="fh-adm-pick-list fh-adm-pick-list--single">
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
                                    <label class="fh-adm-pick-item">
                                        <input type="radio" name="trailer_media_id" value="{{ $media->id }}" @checked((int) old('trailer_media_id', $selected['trailer']) === $media->id)>
                                        <div class="fh-adm-pick-item-body">
                                            <div class="fh-adm-pick-icon"><i class="bi bi-film"></i></div>
                                            <div class="min-w-0">
                                                <div class="fh-adm-pick-name">{{ $media->original_filename }}</div>
                                                <div class="fh-adm-pick-meta">{{ $media->mime_type }}</div>
                                            </div>
                                            <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('trailer_media_id')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label :value="__('Audio clip')" />
                            <div class="fh-adm-pick-list fh-adm-pick-list--single">
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
                                    <label class="fh-adm-pick-item">
                                        <input type="radio" name="audio_clip_media_id" value="{{ $media->id }}" @checked((int) old('audio_clip_media_id', $selected['audio_clip']) === $media->id)>
                                        <div class="fh-adm-pick-item-body">
                                            <div class="fh-adm-pick-icon"><i class="bi bi-music-note-beamed"></i></div>
                                            <div class="min-w-0">
                                                <div class="fh-adm-pick-name">{{ $media->original_filename }}</div>
                                                <div class="fh-adm-pick-meta">{{ $media->mime_type }}</div>
                                            </div>
                                            <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error :messages="$errors->get('audio_clip_media_id')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label :value="__('Gallery images (multi)')" />
                            <div class="fh-adm-pick-list">
                                @forelse ($mediaOptions['images'] as $media)
                                    <label class="fh-adm-pick-item">
                                        <input type="checkbox" name="gallery_media_ids[]" value="{{ $media->id }}" @checked(in_array($media->id, old('gallery_media_ids', $selected['gallery'])))>
                                        <div class="fh-adm-pick-item-body">
                                            <div class="fh-adm-pick-icon"><i class="bi bi-images"></i></div>
                                            <div class="min-w-0">
                                                <div class="fh-adm-pick-name">{{ $media->original_filename }}</div>
                                                <div class="fh-adm-pick-meta">{{ $media->mime_type }}</div>
                                            </div>
                                            <span class="fh-adm-pick-state">{{ __('Add') }}</span>
                                        </div>
                                    </label>
                                @empty
                                    <div class="fh-adm-tile-meta">{{ __('No images in the media library yet.') }}</div>
                                @endforelse
                            </div>
                            <x-input-error :messages="$errors->get('gallery_media_ids')" class="mt-1" />
                        </div>
                        <div class="col-md-12 mb-0">
                            <x-input-label :value="__('Attachments (multi)')" />
                            <div class="fh-adm-pick-list">
                                @forelse ($mediaOptions['documents'] as $media)
                                    <label class="fh-adm-pick-item">
                                        <input type="checkbox" name="attachment_media_ids[]" value="{{ $media->id }}" @checked(in_array($media->id, old('attachment_media_ids', $selected['attachment'])))>
                                        <div class="fh-adm-pick-item-body">
                                            <div class="fh-adm-pick-icon"><i class="bi bi-file-earmark-text"></i></div>
                                            <div class="min-w-0">
                                                <div class="fh-adm-pick-name">{{ $media->original_filename }}</div>
                                                <div class="fh-adm-pick-meta">{{ $media->mime_type }}</div>
                                            </div>
                                            <span class="fh-adm-pick-state">{{ __('Attach') }}</span>
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

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Discovery') }}</h6></div>
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
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Publishing') }}</h6></div>
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
        <div class="card fh-adm-detail-card">
            <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Notes') }}</h6></div>
            <div class="card-body">
                <ul class="mb-0" style="font-size: 0.875rem;">
                    <li class="mb-2">{{ __('Choose the fandom category and publish to show this story on its public fandom page. Admin content defaults to published; a future publishing date schedules the story.') }}</li>
                    <li class="mb-2">{{ __('The cover, excerpt, body, gallery and media appear on the public story page. Featured content receives an Editor\'s Pick badge.') }}</li>
                    <li class="mb-2">{{ __('Slug is auto-generated from the title when left blank.') }}</li>
                    <li class="mb-2">{{ __('Media is selected from the Media Library. Upload files there first.') }}</li>
                    <li class="mb-2">{{ __('Setting status to published stamps published_at when empty.') }}</li>
                    <li class="mb-0">{{ __('User submission metadata is system-controlled and not editable here.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
