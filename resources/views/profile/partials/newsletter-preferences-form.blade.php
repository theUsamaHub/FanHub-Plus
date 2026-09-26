<div class="mb-4">
    <h6 class="fw-semibold mb-3">{{ __('Newsletter Preferences') }}</h6>
    <p class="text-muted mb-4" style="font-size: 0.875rem;">
        {{ __('Choose which fandoms and categories you want to receive email updates about.') }}
    </p>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.newsletter-preferences') }}">
        @csrf

        @php
            $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);
            $subscriber = \App\Models\Subscriber::where('email', auth()->user()->email)->first();
            $preferences = $subscriber?->getPreferences() ?? [];
            $selectedCategories = $preferences['categories'] ?? [];
        @endphp

        <div class="mb-3">
            <label class="form-label">{{ __('Email Address') }}</label>
            <input type="email" class="form-control" value="{{ auth()->user()->email }}" readonly>
            <div class="form-text">{{ __('Newsletters will be sent to this address.') }}</div>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Subscribe to Newsletter') }}</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" name="subscribe" id="subscribeSwitch" {{ $subscriber && $subscriber->isActive() ? 'checked' : '' }}>
                <label class="form-check-label" for="subscribeSwitch">
                    {{ __('I want to receive email newsletters') }}
                </label>
            </div>
        </div>

        <div class="mb-3" id="categoriesField" style="display: {{ $subscriber && $subscriber->isActive() ? 'block' : 'none' }};">
            <label class="form-label">{{ __('Categories / Fandoms') }}</label>
            <select class="form-select" name="categories[]" id="categorySelect" multiple>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ in_array($category->id, $selectedCategories) ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">{{ __('Hold Ctrl/Cmd to select multiple. Empty = all categories.') }}</div>
        </div>

        <div class="mb-3">
            <label class="form-label">{{ __('Frequency') }}</label>
            <select class="form-select" name="frequency" id="frequencySelect">
                <option value="instant" {{ ($preferences['frequency'] ?? 'instant') === 'instant' ? 'selected' : '' }}>
                    {{ __('Instant (as soon as content is published)') }}
                </option>
                <option value="daily" {{ ($preferences['frequency'] ?? '') === 'daily' ? 'selected' : '' }}>
                    {{ __('Daily digest') }}
                </option>
                <option value="weekly" {{ ($preferences['frequency'] ?? '') === 'weekly' ? 'selected' : '' }}>
                    {{ __('Weekly digest') }}
                </option>
            </select>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-1"></i>{{ __('Save Preferences') }}
            </button>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const subscribeSwitch = document.getElementById('subscribeSwitch');
        const categoriesField = document.getElementById('categoriesField');
        
        if (subscribeSwitch && categoriesField) {
            subscribeSwitch.addEventListener('change', function() {
                categoriesField.style.display = this.checked ? 'block' : 'none';
            });
        }
    });
</script>