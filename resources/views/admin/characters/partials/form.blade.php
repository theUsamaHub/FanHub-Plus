@php
    $selectedContentIds = old('content_ids', $selectedContentIds ?? []);
@endphp

<div class="row">
    <div class="col-lg-8">
        <form action="{{ $action }}" method="POST">
            @csrf
            @method($method)

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Profile') }}</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name', $character?->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-input-label for="slug" :value="__('Slug (optional)')" />
                            <x-text-input id="slug" name="slug" type="text" class="form-control" :value="old('slug', $character?->slug)" />
                            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label for="category_id" :value="__('Category')" />
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id', $character?->category_id) === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <x-input-label for="bio" :value="__('Bio')" />
                        <textarea id="bio" name="bio" rows="5" class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $character?->bio) }}</textarea>
                        <x-input-error :messages="$errors->get('bio')" class="mt-1" />
                    </div>
                    <div class="mb-0">
                        <x-input-label :value="__('Image')" />
                        <div class="fh-adm-pick-list fh-adm-pick-list--single">
                            <label class="fh-adm-pick-item">
                                <input type="radio" name="image_media_id" value="" @checked((int) old('image_media_id', $character?->image_media_id) === 0)>
                                <div class="fh-adm-pick-item-body">
                                    <div class="fh-adm-pick-icon"><i class="bi bi-x-lg"></i></div>
                                    <div class="min-w-0">
                                        <div class="fh-adm-pick-name">{{ __('None') }}</div>
                                        <div class="fh-adm-pick-meta">{{ __('No character image') }}</div>
                                    </div>
                                    <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                </div>
                            </label>
                            @forelse ($images as $image)
                                <label class="fh-adm-pick-item">
                                    <input type="radio" name="image_media_id" value="{{ $image->id }}" @checked((int) old('image_media_id', $character?->image_media_id) === $image->id)>
                                    <div class="fh-adm-pick-item-body">
                                        <div class="fh-adm-pick-icon"><i class="bi bi-image"></i></div>
                                        <div class="min-w-0">
                                            <div class="fh-adm-pick-name">{{ $image->original_filename }}</div>
                                            <div class="fh-adm-pick-meta">{{ $image->mime_type ?? 'image' }}</div>
                                        </div>
                                        <span class="fh-adm-pick-state">{{ __('Select') }}</span>
                                    </div>
                                </label>
                            @empty
                                <div class="fh-adm-tile-meta">{{ __('No images in the media library yet.') }}</div>
                            @endforelse
                        </div>
                        <x-input-error :messages="$errors->get('image_media_id')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Related content') }}</h6></div>
                <div class="card-body">
                    <div class="fh-adm-pick-list">
                        @forelse ($contents as $item)
                            <label class="fh-adm-pick-item">
                                <input type="checkbox" name="content_ids[]" value="{{ $item->id }}" @checked(in_array($item->id, $selectedContentIds))>
                                <div class="fh-adm-pick-item-body">
                                    <div class="fh-adm-pick-icon"><i class="bi bi-link-45deg"></i></div>
                                    <div class="min-w-0">
                                        <div class="fh-adm-pick-name">{{ \Illuminate\Support\Str::limit($item->title, 60) }}</div>
                                        <div class="fh-adm-pick-meta">{{ $item->type }} · {{ $item->status }}</div>
                                    </div>
                                    <span class="fh-adm-pick-state">{{ __('Link') }}</span>
                                </div>
                            </label>
                        @empty
                            <div class="fh-adm-tile-meta">{{ __('No content available to link.') }}</div>
                        @endforelse
                    </div>
                    <x-input-error :messages="$errors->get('content_ids')" class="mt-1" />
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.characters.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                <x-primary-button>{{ $character ? __('Update Character') : __('Create Character') }}</x-primary-button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card fh-adm-detail-card">
            <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Notes') }}</h6></div>
            <div class="card-body">
                <ul class="mb-0" style="font-size: 0.875rem;">
                    <li class="mb-2">{{ __('Category is required and must already exist.') }}</li>
                    <li class="mb-2">{{ __('Image is selected from the Media Library.') }}</li>
                    <li class="mb-0">{{ __('Related content can also be managed from the character detail page.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>
