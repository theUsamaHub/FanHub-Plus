@php
    $selectedContentIds = old('content_ids', $selectedContentIds ?? []);
@endphp

<div class="row">
    <div class="col-lg-8">
        <form action="{{ $action }}" method="POST">
            @csrf
            @method($method)

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Profile') }}</h6></div>
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
                        <x-input-label for="image_media_id" :value="__('Image')" />
                        <select name="image_media_id" id="image_media_id" class="form-select">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($images as $image)
                                <option value="{{ $image->id }}" @selected((int) old('image_media_id', $character?->image_media_id) === $image->id)>{{ $image->original_filename }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('image_media_id')" class="mt-1" />
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Related content') }}</h6></div>
                <div class="card-body">
                    <select name="content_ids[]" class="form-select" multiple size="8">
                        @foreach ($contents as $item)
                            <option value="{{ $item->id }}" @selected(in_array($item->id, $selectedContentIds))>{{ $item->title }} ({{ $item->type }})</option>
                        @endforeach
                    </select>
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
            <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Notes') }}</h6></div>
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
