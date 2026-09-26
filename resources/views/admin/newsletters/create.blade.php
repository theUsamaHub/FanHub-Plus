@extends('layouts.app')

@section('content')
    <div class="mb-4 fh-adm-page-head">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h2 class="h4 mb-0 fw-semibold">{{ __('Create Newsletter') }}</h2>
                <p class="text-muted mb-0 mt-1" style="font-size:.8rem;">{{ __('Compose and send an email newsletter to your subscribers.') }}</p>
            </div>
            <a href="{{ route('admin.newsletters.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>{{ __('Back') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Email Content') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.newsletters.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">{{ __('Subject') }}</label>
                            <input type="text" class="form-control" name="subject" required maxlength="255"
                                   placeholder="{{ __('Enter email subject') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Email Body (HTML supported)') }}</label>
                            <textarea class="form-control" name="body" rows="15" required
                                      placeholder="{{ __('Write your email content here...') }}"></textarea>
                            <div class="form-text">{{ __('You can use HTML tags for formatting.') }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Type') }}</label>
                            <select class="form-select" name="type" id="newsletterType">
                                <option value="custom">{{ __('Custom Newsletter') }}</option>
                                <option value="content">{{ __('Content (Anime/Manga/Article)') }}</option>
                                <option value="event">{{ __('Event') }}</option>
                                <option value="character">{{ __('Character') }}</option>
                                <option value="merchandise">{{ __('Merchandise') }}</option>
                                <option value="category">{{ __('Category/Fandom') }}</option>
                            </select>
                        </div>

                        <div class="mb-3" id="referenceField" style="display: none;">
                            <label class="form-label">{{ __('Reference Item') }}</label>
                            <select class="form-select" name="reference_id" id="referenceSelect">
                                <option value="">{{ __('Select an item...') }}</option>
                            </select>
                            <div class="form-text">{{ __('Optional: Link this newsletter to a specific item.') }}</div>
                        </div>

                        <div class="mb-3" id="filterField" style="display: none;">
                            <label class="form-label">{{ __('Recipient Filters') }}</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Categories/Fandoms') }}</label>
                                    <select class="form-select" name="recipient_filters[categories][]" id="categoryFilter" multiple>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">{{ __('Only send to subscribers interested in these categories. Hold Ctrl/Cmd to select multiple.') }}</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Subscriber Status') }}</label>
                                    <select class="form-select" name="recipient_filters[status]">
                                        <option value="active">{{ __('Active subscribers only') }}</option>
                                        <option value="unsubscribed">{{ __('Unsubscribed') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>{{ __('Save as Draft') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card fh-adm-form-card">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Tips') }}</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 text-muted" style="font-size:.85rem;">
                        <li class="mb-2">{{ __('Use <code>{name}</code> to personalize with subscriber name.') }}</li>
                        <li class="mb-2">{{ __('Use <code>{unsubscribe_url}</code> for unsubscribe link.') }}</li>
                        <li class="mb-2">{{ __('Select a Type to auto-populate reference items.') }}</li>
                        <li class="mb-2">{{ __('Use Category filters to target specific fandoms.') }}</li>
                        <li class="mb-2">{{ __('Save as draft first, then preview before sending.') }}</li>
                    </ul>
                </div>
            </div>

            <div class="card fh-adm-form-card mt-4">
                <div class="card-header">
                    <h6 class="mb-0 fw-semibold fh-adm-section-title">{{ __('Subscriber Stats') }}</h6>
                </div>
                <div class="card-body">
                    @php
                        $activeCount = \App\Models\Subscriber::where('status', 'active')->count();
                        $totalCount = \App\Models\Subscriber::count();
                    @endphp
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('Total Subscribers') }}</span>
                        <span class="fw-semibold">{{ $totalCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">{{ __('Active') }}</span>
                        <span class="fw-semibold text-success">{{ $activeCount }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">{{ __('Unsubscribed') }}</span>
                        <span class="fw-semibold text-secondary">{{ $totalCount - $activeCount }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('newsletterType');
        const referenceField = document.getElementById('referenceField');
        const referenceSelect = document.getElementById('referenceSelect');
        const filterField = document.getElementById('filterField');

        const references = {
            content: @json(\App\Models\Content::orderBy('title')->get(['id', 'title'])),
            event: @json(\App\Models\Event::orderBy('start_at', 'desc')->get(['id', 'title'])),
            character: @json(\App\Models\CharacterProfile::orderBy('name')->get(['id', 'name'])),
            merchandise: @json(\App\Models\MerchandiseItem::orderBy('name')->get(['id', 'name'])),
            category: @json(\App\Models\Category::orderBy('name')->get(['id', 'name'])),
        };

        typeSelect.addEventListener('change', function() {
            const type = this.value;
            referenceSelect.innerHTML = '<option value="">Select an item...</option>';

            if (references[type]) {
                references[type].forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.title || item.name;
                    referenceSelect.appendChild(option);
                });
                referenceField.style.display = 'block';
            } else {
                referenceField.style.display = 'none';
            }

            // Show filters for custom, content, event, character, merchandise, category
            if (type !== 'custom') {
                filterField.style.display = 'block';
            } else {
                filterField.style.display = 'none';
            }
        });
    });
</script>
@endpush