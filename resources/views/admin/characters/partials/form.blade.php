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
                        @include('admin.partials.media-image-picker', [
                            'images' => $images,
                            'categories' => $categories,
                            'selectedId' => (int) old('image_media_id', $character?->image_media_id),
                            'inputName' => 'image_media_id',
                            'label' => __('Image'),
                            'description' => __('Select one image to represent this character.'),
                            'noneLabel' => __('No character image'),
                        ])
                    </div>
                </div>
            </div>

            <div class="card mb-4 fh-adm-form-card">
                <div class="card-header"><h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Related content') }}</h6></div>
                <div class="card-body">
                    <p class="text-muted small mb-2">{{ __('Select one or more content entries. The list is filtered by the chosen category.') }}</p>
                    <div class="fh-adm-pick-list" data-related-content-list
                        data-lookup-url-template="{{ route('admin.lookups.contents-by-category', ['category' => '__CAT__']) }}"
                        data-selected='@json($selectedContentIds ?? [])'>
                        @forelse ($contents as $item)
                            <label class="fh-adm-pick-item" data-content-id="{{ $item->id }}" data-category-id="{{ $item->category_id }}">
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
                            <div class="fh-adm-tile-meta" data-empty-state>{{ __('No content available to link.') }}</div>
                        @endforelse
                    </div>
                    <x-input-error :messages="$errors->get('content_ids')" class="mt-1" />
                    <x-input-error :messages="$errors->get('content_ids.*')" class="mt-1" />
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
                    <li class="mb-2">{{ __('Related content list reloads when you change the category.') }}</li>
                    <li class="mb-0">{{ __('A character must be linked to at least one content.') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var categorySelect = document.getElementById('category_id');
    if (!categorySelect) return;
    var form = categorySelect.closest('form');
    if (!form) return;
    var list = form.querySelector('[data-related-content-list]');
    if (!list) return;

    var lookupTemplate = list.getAttribute('data-lookup-url-template') || '';
    var selectedIds = [];
    try { selectedIds = JSON.parse(list.getAttribute('data-selected') || '[]'); } catch (e) { selectedIds = []; }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function renderEmpty(message) {
        list.innerHTML = '<div class="fh-adm-tile-meta" data-empty-state>' + escapeHtml(message) + '</div>';
    }

    function renderOptions(contents) {
        if (!contents.length) { renderEmpty('No content available to link.'); return; }
        var html = '';
        contents.forEach(function (c) {
            var checked = selectedIds.indexOf(String(c.id)) !== -1 || selectedIds.indexOf(Number(c.id)) !== -1;
            var title = (c.title || '').length > 60 ? c.title.slice(0, 60) + '…' : c.title;
            html += '<label class="fh-adm-pick-item" data-content-id="' + c.id + '" data-category-id="' + (c.category_id || '') + '">'
                + '<input type="checkbox" name="content_ids[]" value="' + c.id + '"' + (checked ? ' checked' : '') + '>'
                + '<div class="fh-adm-pick-item-body">'
                + '<div class="fh-adm-pick-icon"><i class="bi bi-link-45deg"></i></div>'
                + '<div class="min-w-0">'
                + '<div class="fh-adm-pick-name">' + escapeHtml(title) + '</div>'
                + '<div class="fh-adm-pick-meta">' + escapeHtml(c.type || '') + ' · ' + escapeHtml(c.status || '') + '</div>'
                + '</div>'
                + '<span class="fh-adm-pick-state">Link</span>'
                + '</div></label>';
        });
        list.innerHTML = html;
    }

    function reload() {
        var cat = categorySelect.value;
        if (!cat || !lookupTemplate) {
            // No category yet — keep server-rendered list intact.
            return;
        }
        var url = lookupTemplate.replace('__CAT__', encodeURIComponent(cat));
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (json) {
                if (!json || !Array.isArray(json.contents)) { renderEmpty('No content available to link.'); return; }
                renderOptions(json.contents);
            })
            .catch(function () { renderEmpty('No content available to link.'); });
    }

    categorySelect.addEventListener('change', function () {
        // Clear stale selections that don't belong to the new category.
        selectedIds = [];
        reload();
    });
});
</script>
@endpush
