@php
    $pickerImages = $images ?? collect();
    $pickerCategories = $categories ?? collect();
    $pickerInput = $inputName ?? 'image_media_id';
    $pickerSelected = (int) ($selectedId ?? 0);
    $pickerLabel = $label ?? __('Image');
    $pickerDesc = $description ?? __('Select one image from the media library.');
    $pickerNone = $noneLabel ?? __('No image');
    $pickerEmpty = $emptyLabel ?? __('No images in the media library yet.');

    $pickerPayload = [
        'images' => $pickerImages->map(fn ($media) => [
            'id' => $media->id,
            'name' => $media->original_filename,
            'mime' => $media->mime_type,
            'ext' => strtoupper(pathinfo((string) $media->original_filename, PATHINFO_EXTENSION)),
            'size' => $media->size_formatted,
            'duration' => null,
            'dimensions' => ($media->width && $media->height) ? $media->width.'×'.$media->height : null,
            'url' => $media->hasValidPath() ? $media->url : null,
            'download' => $media->hasValidPath() ? route('admin.media.download', $media) : null,
            'category_id' => $media->category_id,
            'category' => $media->category?->name,
        ])->values()->all(),
    ];
@endphp

<div class="fh-adm-media-block" data-fh-image-picker>
    <div class="fh-adm-media-block-head">
        <div class="fh-adm-media-block-head-copy">
            <span class="fh-adm-media-block-icon" data-tone="images"><i class="bi bi-image"></i></span>
            <div class="min-w-0">
                <div class="fh-adm-media-block-title">{{ $pickerLabel }}</div>
                <div class="fh-adm-media-block-desc">{{ $pickerDesc }}</div>
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary fh-adm-view-all" data-fh-media="image">
            <i class="bi bi-arrows-fullscreen me-1"></i>{{ __('View all') }}
        </button>
    </div>

    <div class="fh-adm-pick-list fh-adm-pick-grid" data-fh-list="fh-image" data-pick="single">
        <label class="fh-adm-pick-item fh-adm-pick-card">
            <input type="radio" name="{{ $pickerInput }}" value="" @checked($pickerSelected === 0)>
            <div class="fh-adm-pick-card-body">
                <div class="fh-adm-pick-thumb fh-adm-pick-thumb--none"><i class="bi bi-x-lg"></i><span class="fh-adm-pick-check"><i class="bi bi-check-lg"></i></span></div>
                <div class="fh-adm-pick-name">{{ __('None') }}</div>
                <div class="fh-adm-pick-meta">{{ $pickerNone }}</div>
            </div>
        </label>
        @forelse ($pickerImages as $media)
            <label class="fh-adm-pick-item fh-adm-pick-card">
                <input type="radio" name="{{ $pickerInput }}" value="{{ $media->id }}" @checked($pickerSelected === $media->id)>
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
            <div class="fh-adm-tile-meta">{{ $pickerEmpty }}</div>
        @endforelse
    </div>

    <x-input-error :messages="$errors->get($pickerInput)" class="mt-1" />
</div>

@push('modals')
<div class="fh-adm-media-modal" id="fhImagePickerModal" role="dialog" aria-modal="true" aria-labelledby="fhImagePickerTitle">
    <div class="fh-adm-media-modal__backdrop" data-fh-media-close></div>
    <div class="fh-adm-media-modal__panel">
        <div class="fh-adm-media-modal__head">
            <h5 class="fh-adm-media-modal__title" id="fhImagePickerTitle">{{ __('Select Image') }}</h5>
            <button type="button" class="fh-adm-media-modal__close" data-fh-media-close aria-label="{{ __('Close') }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="fh-adm-media-modal__body">
            <div class="fh-adm-media-tabs" id="fhImagePickerTabs" role="tablist" aria-label="{{ __('Filter by category') }}"></div>
            <div class="fh-adm-media-search">
                <i class="bi bi-search"></i>
                <input type="search" class="form-control" id="fhImagePickerSearch" placeholder="{{ __('Search images...') }}" autocomplete="off">
            </div>
            <div class="fh-adm-media-results fh-adm-media-results--grid" id="fhImagePickerResults" role="listbox"></div>
        </div>
        <div class="fh-adm-media-modal__foot">
            <span class="fh-adm-media-modal-count" id="fhImagePickerCount"></span>
            <button type="button" class="btn btn-outline-secondary" data-fh-media-close>{{ __('Cancel') }}</button>
            <button type="button" class="btn btn-primary" id="fhImagePickerUse">{{ __('Use Selected') }}</button>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('fhImagePickerModal');
    var list = document.querySelector('[data-fh-list="fh-image"]');
    if (!modalEl || !list) return;
    var form = list.closest('form');
    if (!form) return;

    var PAYLOAD = @json($pickerPayload);
    var CATEGORIES = @json($pickerCategories->map(fn ($c) => ['id' => (int) $c->id, 'name' => $c->name])->values());
    var INPUT = @json($pickerInput);
    var L = {
        selected: @json(__('selected')),
        noResults: @json(__('No media matches your search.')),
        untitled: @json(__('Untitled')),
        all: @json(__('All Images')),
        uncategorized: @json(__('Uncategorized'))
    };

    var els = {
        title: document.getElementById('fhImagePickerTitle'),
        tabs: document.getElementById('fhImagePickerTabs'),
        search: document.getElementById('fhImagePickerSearch'),
        results: document.getElementById('fhImagePickerResults'),
        count: document.getElementById('fhImagePickerCount'),
        use: document.getElementById('fhImagePickerUse')
    };
    if (!els.title || !els.tabs || !els.search || !els.results || !els.count || !els.use) return;

    var activeCat = 'all';
    var searchText = '';
    var draft = [];

    function esc(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function inputsFor() {
        return Array.prototype.slice.call(form.querySelectorAll('[name="' + INPUT + '"]'));
    }

    function readSelection() {
        return inputsFor()
            .filter(function (input) { return input.checked && input.value !== ''; })
            .map(function (input) { return Number(input.value); });
    }

    function findItem(id) {
        var items = PAYLOAD.images || [];
        for (var i = 0; i < items.length; i++) if (items[i].id === id) return items[i];
        return null;
    }

    function ensureInline(id) {
        var exists = inputsFor().some(function (input) { return Number(input.value) === id; });
        if (exists) return;
        var item = findItem(id);
        if (!item) return;
        var thumb = item.url ? '<img src="' + esc(item.url) + '" alt="" loading="lazy">' : '<i class="bi bi-image"></i>';
        list.insertAdjacentHTML('beforeend',
            '<label class="fh-adm-pick-item fh-adm-pick-card"><input type="radio" name="' + esc(INPUT) + '" value="' + item.id + '" checked>'
            + '<div class="fh-adm-pick-card-body"><div class="fh-adm-pick-thumb">' + thumb
            + '<span class="fh-adm-pick-check"><i class="bi bi-check-lg"></i></span></div>'
            + '<div class="fh-adm-pick-name" title="' + esc(item.name || '') + '">' + esc(item.name || L.untitled) + '</div>'
            + '<div class="fh-adm-pick-meta">' + esc(item.mime || item.size || '') + '</div></div></label>');
    }

    function writeSelection(ids) {
        inputsFor().forEach(function (input) {
            input.checked = ids.length ? Number(input.value) === ids[0] : input.value === '';
        });
        ids.forEach(function (id) { ensureInline(id); });
    }

    function updateModalCount() {
        els.count.textContent = draft.length + ' ' + L.selected;
    }

    function tabsFor() {
        var items = PAYLOAD.images || [];
        var counts = {};
        items.forEach(function (item) {
            var key = item.category_id ? String(item.category_id) : 'none';
            counts[key] = (counts[key] || 0) + 1;
        });

        var tabs = [{ id: 'all', label: L.all, count: items.length }];
        var known = {};
        CATEGORIES.forEach(function (category) {
            var id = String(category.id);
            known[id] = true;
            tabs.push({ id: id, label: category.name, count: counts[id] || 0 });
        });
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
        if (counts.none) tabs.push({ id: 'none', label: L.uncategorized, count: counts.none });

        return tabs;
    }

    function renderTabs() {
        var tabs = tabsFor();
        els.tabs.style.display = tabs.length > 1 ? '' : 'none';
        els.tabs.innerHTML = tabs.map(function (tab) {
            var active = String(activeCat) === String(tab.id);
            return '<button type="button" class="fh-adm-media-tab' + (active ? ' is-active' : '') + '" role="tab"'
                + ' data-cat="' + esc(tab.id) + '" aria-selected="' + (active ? 'true' : 'false') + '">'
                + '<span>' + esc(tab.label) + '</span>'
                + '<span class="fh-adm-media-tab-count">' + tab.count + '</span></button>';
        }).join('');
    }

    function filteredItems() {
        var query = searchText.trim().toLowerCase();
        return (PAYLOAD.images || []).filter(function (item) {
            if (activeCat === 'all') {

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

    function renderResults() {
        var items = filteredItems();

        if (!items.length) {
            els.results.innerHTML = '<div class="fh-adm-media-empty"><i class="bi bi-folder2"></i><p>' + esc(L.noResults) + '</p></div>';
            updateModalCount();
            return;
        }

        els.results.innerHTML = items.map(function (item) {
            var on = draft.indexOf(item.id) !== -1;
            var thumb = item.url
                ? '<img src="' + esc(item.url) + '" alt="" loading="lazy">'
                : '<i class="bi bi-image"></i>';
            var cat = item.category ? '<span class="fh-adm-media-cat">' + esc(item.category) + '</span>' : '';
            return '<div class="fh-adm-media-card' + (on ? ' is-selected' : '') + '" data-id="' + item.id + '" role="option" tabindex="0"'
                + ' aria-selected="' + (on ? 'true' : 'false') + '" title="' + esc(item.name) + '">'
                + '<div class="fh-adm-media-thumb">' + thumb + '<span class="fh-adm-media-check"><i class="bi bi-check-lg"></i></span></div>'
                + '<div class="fh-adm-media-name">' + esc(item.name || L.untitled) + '</div>'
                + '<div class="fh-adm-media-meta">' + esc(item.size || '') + cat + '</div>'
                + '</div>';
        }).join('');

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
        draft = [id];
        applyVisuals();
    }

    function prepare() {
        activeCat = 'all';
        searchText = '';
        draft = readSelection();
        els.search.value = '';
        renderTabs();
        renderResults();
    }

    function openModal() {
        modalEl.classList.add('is-open');
        document.body.classList.add('fh-adm-modal-open');
        els.search.focus();
    }

    function closeModal() {
        modalEl.classList.remove('is-open');
        document.body.classList.remove('fh-adm-modal-open');
        draft = [];
    }

    Array.prototype.forEach.call(document.querySelectorAll('[data-fh-media="image"]'), function (trigger) {
        trigger.addEventListener('click', function () {
            prepare();
            openModal();
        });
    });

    modalEl.addEventListener('click', function (event) {
        var close = event.target && event.target.closest ? event.target.closest('[data-fh-media-close]') : null;
        if (close) closeModal();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape' || !modalEl.classList.contains('is-open')) return;
        closeModal();
    });

    els.tabs.addEventListener('click', function (event) {
        var tab = event.target && event.target.closest ? event.target.closest('[data-cat]') : null;
        if (!tab) return;
        activeCat = tab.getAttribute('data-cat');
        renderTabs();
        renderResults();
    });

    els.search.addEventListener('input', function () {
        searchText = this.value;
        renderResults();
    });

    els.results.addEventListener('click', function (event) {
        var node = event.target && event.target.closest ? event.target.closest('[data-id]') : null;
        if (!node) return;
        pick(Number(node.getAttribute('data-id')));
    });

    els.results.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;
        var node = event.target && event.target.closest ? event.target.closest('[data-id]') : null;
        if (!node) return;
        event.preventDefault();
        pick(Number(node.getAttribute('data-id')));
    });

    els.use.addEventListener('click', function () {
        writeSelection(draft);
        closeModal();
    });
});
</script>
@endpush
