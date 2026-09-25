@php
    $isPublished = old('is_published', $release?->is_published ?? true);
@endphp

<div class="row">
    <div class="col-lg-8">
        <form action="{{ $action }}" method="POST">
            @csrf
            @method($method)

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Release Details') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <x-input-label for="title" :value="__('Title')" />
                        <x-text-input id="title" name="title" type="text" class="form-control" :value="old('title', $release?->title)" required />
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <x-input-label for="slug" :value="__('Slug (optional)')" />
                            <x-text-input id="slug" name="slug" type="text" class="form-control" :value="old('slug', $release?->slug)" />
                            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-input-label for="kind" :value="__('Kind')" />
                            <select name="kind" id="kind" class="form-select @error('kind') is-invalid @enderror" required>
                                @foreach (\App\Models\UpcomingRelease::KINDS as $kind)
                                    <option value="{{ $kind }}" @selected(old('kind', $release?->kind ?? 'anime') === $kind)>{{ ucwords($kind) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('kind')" class="mt-1" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-input-label for="category_id" :value="__('Category (optional)')" />
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id', $release?->category_id) === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-input-label for="release_date" :value="__('Release date')" />
                            <x-text-input id="release_date" name="release_date" type="date" class="form-control" :value="old('release_date', $release?->release_date?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('release_date')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label for="release_label" :value="__('Release label')" />
                            <x-text-input id="release_label" name="release_label" type="text" class="form-control" :value="old('release_label', $release?->release_label)" placeholder="{{ __('e.g. Premiere, Season 2, In theaters') }}" />
                            <x-input-error :messages="$errors->get('release_label')" class="mt-1" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <x-input-label for="description" :value="__('Description (optional)')" />
                        <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $release?->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="image_media_id" :value="__('Cover image')" />
                        <select name="image_media_id" id="image_media_id" class="form-select">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($images as $image)
                                <option value="{{ $image->id }}" @selected((int) old('image_media_id', $release?->image_media_id) === $image->id)>{{ $image->original_filename }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('image_media_id')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Publishing') }}</h6></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-input-label for="sort_order" :value="__('Sort order')" />
                            <x-text-input id="sort_order" name="sort_order" type="number" min="0" max="9999" class="form-control" :value="old('sort_order', $release?->sort_order ?? 0)" />
                            <x-input-error :messages="$errors->get('sort_order')" class="mt-1" />
                        </div>
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" @checked($isPublished)>
                                <label class="form-check-label" for="is_published">{{ __('Published (show on Upcoming page)') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.upcoming-releases.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                <x-primary-button>{{ $release ? __('Update Release') : __('Create Release') }}</x-primary-button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card fh-adm-detail-card">
            <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Tips') }}</h6></div>
            <div class="card-body">
                <ul class="mb-0" style="font-size: 0.875rem;">
                    <li class="mb-2">{{ __('Published releases with a future (or empty) date appear on the public Upcoming page and home carousel.') }}</li>
                    <li class="mb-2">{{ __('Leave the date empty for “Date TBA” entries.') }}</li>
                    <li class="mb-2">{{ __('Upload cover images in Media Library first, then pick them here.') }}</li>
                    <li class="mb-0">{{ __('Kind drives the badge: Anime, Event, Movie, Series, Game, Merchandise.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
