@php
    $isUpcoming = old('is_upcoming', $item?->is_upcoming);
@endphp

<div class="row">
    <div class="col-lg-8">
        <form action="{{ $action }}" method="POST">
            @csrf
            @method($method)

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-body">
                    <div class="mb-3">
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="form-control" :value="old('name', $item?->name)" required />
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <x-input-label for="slug" :value="__('Slug (optional)')" />
                            <x-text-input id="slug" name="slug" type="text" class="form-control" :value="old('slug', $item?->slug)" />
                            <x-input-error :messages="$errors->get('slug')" class="mt-1" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-input-label for="category_id" :value="__('Category')" />
                            <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">{{ __('Select category') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((int) old('category_id', $item?->category_id) === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                        </div>
                        <div class="col-md-4 mb-3">
                            <x-input-label for="tag" :value="__('Tag')" />
                            <select name="tag" id="tag" class="form-select @error('tag') is-invalid @enderror" required>
                                @foreach (['limited_edition', 'pre_order', 'collectible', 'standard'] as $tagOption)
                                    <option value="{{ $tagOption }}" @selected(old('tag', $item?->tag ?? 'standard') === $tagOption)>{{ ucwords(str_replace('_', ' ', $tagOption)) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tag')" class="mt-1" />
                        </div>
                    </div>
                    <div class="mb-3">
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $item?->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                    <div class="mb-3">
                        <x-input-label for="image_media_id" :value="__('Image')" />
                        <select name="image_media_id" id="image_media_id" class="form-select">
                            <option value="">{{ __('None') }}</option>
                            @foreach ($images as $image)
                                <option value="{{ $image->id }}" @selected((int) old('image_media_id', $item?->image_media_id) === $image->id)>{{ $image->original_filename }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('image_media_id')" class="mt-1" />
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_upcoming" value="1" id="is_upcoming" @checked($isUpcoming)>
                        <label class="form-check-label" for="is_upcoming">{{ __('Upcoming item') }}</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-4">
                <a href="{{ route('admin.merchandise.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
                <x-primary-button>{{ $item ? __('Update Merchandise') : __('Create Merchandise') }}</x-primary-button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <div class="card fh-adm-detail-card">
            <div class="card-header"><h6 class="mb-0 fw-semibold">{{ __('Scope') }}</h6></div>
            <div class="card-body">
                <p class="mb-0 text-muted" style="font-size: 0.875rem;">
                    {{ __('Merchandise is display-only. Do not add stock, pricing, checkout, or order management.') }}
                </p>
            </div>
        </div>
    </div>
</div>
