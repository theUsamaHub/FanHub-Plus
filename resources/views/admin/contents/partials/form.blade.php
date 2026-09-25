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
                            <x-input-label for="cover_media_id" :value="__('Cover')" />
                            <select name="cover_media_id" id="cover_media_id" class="form-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($mediaOptions['images'] as $media)
                                    <option value="{{ $media->id }}" @selected((int) old('cover_media_id', $selected['cover']) === $media->id)>{{ $media->original_filename }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('cover_media_id')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label for="trailer_media_id" :value="__('Trailer')" />
                            <select name="trailer_media_id" id="trailer_media_id" class="form-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($mediaOptions['videos'] as $media)
                                    <option value="{{ $media->id }}" @selected((int) old('trailer_media_id', $selected['trailer']) === $media->id)>{{ $media->original_filename }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('trailer_media_id')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label for="audio_clip_media_id" :value="__('Audio clip')" />
                            <select name="audio_clip_media_id" id="audio_clip_media_id" class="form-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($mediaOptions['audio'] as $media)
                                    <option value="{{ $media->id }}" @selected((int) old('audio_clip_media_id', $selected['audio_clip']) === $media->id)>{{ $media->original_filename }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('audio_clip_media_id')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label for="gallery_media_ids" :value="__('Gallery (multi)')" />
                            <select name="gallery_media_ids[]" id="gallery_media_ids" class="form-select" multiple size="5">
                                @foreach ($mediaOptions['images'] as $media)
                                    <option value="{{ $media->id }}" @selected(in_array($media->id, old('gallery_media_ids', $selected['gallery'])))>{{ $media->original_filename }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('gallery_media_ids')" class="mt-1" />
                        </div>
                        <div class="col-md-12 mb-0">
                            <x-input-label for="attachment_media_ids" :value="__('Attachments (multi)')" />
                            <select name="attachment_media_ids[]" id="attachment_media_ids" class="form-select" multiple size="5">
                                @foreach ($mediaOptions['documents'] as $media)
                                    <option value="{{ $media->id }}" @selected(in_array($media->id, old('attachment_media_ids', $selected['attachment'])))>{{ $media->original_filename }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('attachment_media_ids')" class="mt-1" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Discovery') }}</h6></div>
                <div class="card-body">
                    <x-input-label for="tags" :value="__('Tags')" />
                    <select name="tags[]" id="tags" class="form-select" multiple size="5">
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" @selected(in_array($tag->id, $selectedTags))>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('tags')" class="mt-1" />
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
                                    <option value="{{ $status }}" @selected(old('status', $content?->status ?? 'draft') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label for="published_at" :value="__('Published at')" />
                            <x-text-input id="published_at" name="published_at" type="datetime-local" class="form-control" :value="old('published_at', $content?->published_at?->format('Y-m-d\TH:i'))" />
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
                    <li class="mb-2">{{ __('Slug is auto-generated from the title when left blank.') }}</li>
                    <li class="mb-2">{{ __('Media is selected from the Media Library. Upload files there first.') }}</li>
                    <li class="mb-2">{{ __('Setting status to published stamps published_at when empty.') }}</li>
                    <li class="mb-0">{{ __('User submission metadata is system-controlled and not editable here.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
