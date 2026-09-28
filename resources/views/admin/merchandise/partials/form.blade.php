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

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <x-input-label for="content_id" :value="__('Content (required)')" />
                            <select name="content_id" id="content_id" class="form-select @error('content_id') is-invalid @enderror"
                                data-merch-content-select
                                data-lookup-url-template="{{ route('admin.lookups.contents-by-category', ['category' => '__CAT__']) }}">
                                <option value="">{{ __('Select content') }}</option>
                                @foreach (($contents ?? collect()) as $contentItem)
                                    <option value="{{ $contentItem->id }}" @selected((int) old('content_id', $item?->content_id) === $contentItem->id)>{{ \Illuminate\Support\Str::limit($contentItem->title, 80) }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('content_id')" class="mt-1" />
                        </div>
                        <div class="col-md-6 mb-3">
                            <x-input-label for="character_id" :value="__('Character (optional)')" />
                            <select name="character_id" id="character_id" class="form-select @error('character_id') is-invalid @enderror"
                                data-merch-character-select
                                data-lookup-url-template="{{ route('admin.lookups.characters-by-content', ['content' => '__CONTENT__']) }}"
                                @disabled(!old('content_id', $item?->content_id))>
                                <option value="">{{ __('No specific character') }}</option>
                                @foreach (($characters ?? collect()) as $characterItem)
                                    <option value="{{ $characterItem->id }}" @selected((int) old('character_id', $item?->character_id) === $characterItem->id)>{{ $characterItem->name }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('character_id')" class="mt-1" />
                        </div>
                    </div>

                    <div class="mb-3">
                        <x-input-label for="description" :value="__('Description')" />
                        <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $item?->description) }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                    <div class="mb-3">
                        @include('admin.partials.media-image-picker', [
                            'images' => $images,
                            'categories' => $categories,
                            'selectedId' => (int) old('image_media_id', $item?->image_media_id),
                            'inputName' => 'image_media_id',
                            'label' => __('Image'),
                            'description' => __('Select one image to represent this product.'),
                            'noneLabel' => __('No product image'),
                        ])
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
            <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Scope') }}</h6></div>
            <div class="card-body">
                <p class="mb-2 text-muted" style="font-size: 0.875rem;">
                    {{ __('Every merchandise item must belong to a Content. A Character is optional — leave it blank for content-level merchandise.') }}
                </p>
                <p class="mb-0 text-muted" style="font-size: 0.875rem;">
                    {{ __('Merchandise is display-only. Do not add stock, pricing, checkout, or order management.') }}
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var categorySelect = document.getElementById('category_id');
    var contentSelect = document.getElementById('content_id');
    var characterSelect = document.getElementById('character_id');
    if (!categorySelect || !contentSelect || !characterSelect) return;

    var contentTemplate = contentSelect.getAttribute('data-lookup-url-template') || '';
    var characterTemplate = characterSelect.getAttribute('data-lookup-url-template') || '';
    var characterBaseOptions = '<option value="">{{ __('No specific character') }}</option>';

    function escapeAttr(value) {
        return String(value == null ? '' : value).replace(/"/g, '&quot;');
    }

    function resetCharacter(message) {
        characterSelect.innerHTML = characterBaseOptions + (message ? '<option value="" disabled>' + message + '</option>' : '');
        characterSelect.disabled = true;
    }

    function setContentOptions(contents) {
        var html = '<option value="">{{ __('Select content') }}</option>';
        contents.forEach(function (c) {
            html += '<option value="' + c.id + '">' + escapeAttr((c.title || '').slice(0, 80)) + '</option>';
        });
        contentSelect.innerHTML = html;
    }

    function setCharacterOptions(characters) {
        var html = characterBaseOptions;
        characters.forEach(function (c) {
            html += '<option value="' + c.id + '">' + escapeAttr(c.name) + '</option>';
        });
        characterSelect.innerHTML = html;
        characterSelect.disabled = characters.length === 0;
    }

    function reloadContent() {
        var cat = categorySelect.value;
        if (!cat || !contentTemplate) return;
        var url = contentTemplate.replace('__CAT__', encodeURIComponent(cat));
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (json) {
                if (!json || !Array.isArray(json.contents)) { setContentOptions([]); return; }
                setContentOptions(json.contents);
            })
            .catch(function () { setContentOptions([]); });
    }

    function reloadCharacters() {
        var contentId = contentSelect.value;
        if (!contentId || !characterTemplate) {
            resetCharacter(contentId ? 'No characters linked to this content.' : 'Pick a content first.');
            return;
        }
        var url = characterTemplate.replace('__CONTENT__', encodeURIComponent(contentId));
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (json) {
                if (!json || !Array.isArray(json.characters)) { setCharacterOptions([]); return; }
                setCharacterOptions(json.characters);
            })
            .catch(function () { setCharacterOptions([]); });
    }

    categorySelect.addEventListener('change', function () {

        contentSelect.value = '';
        characterSelect.value = '';
        resetCharacter('Pick a content first.');
        reloadContent();
    });

    contentSelect.addEventListener('change', function () {
        characterSelect.value = '';
        reloadCharacters();
    });
});
</script>
@endpush