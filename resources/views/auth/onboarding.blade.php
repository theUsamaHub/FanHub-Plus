<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Welcome to FanHub+') }} — {{ config('app.name', 'FanHubPlus') }}</title>
    <script>try { document.documentElement.dataset.theme = localStorage.getItem('fanhub-theme') === 'light' ? 'light' : 'dark'; } catch (e) {}</script>
    @vite(['resources/css/onboarding.css', 'resources/js/public.js'])
</head>
<body class="fh-site">
    <div class="fh-onboarding min-vh-100 d-flex align-items-center justify-content-center px-4 py-6">
        <div class="fh-onboarding-card w-100" style="max-width: 720px;">
            <div class="fh-onboarding-header text-center mb-5">
                <div class="fh-onboarding-logo mx-auto mb-5" role="img" aria-label="FanHub+ Logo">
                    <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" width="64" height="64" aria-hidden="true">
                        <defs>
                            <linearGradient id="onboarding-gradient" x1="0" y1="0" x2="64" y2="64" gradientUnits="userSpaceOnUse">
                                <stop offset="0%" stop-color="#FF922E"/>
                                <stop offset="100%" stop-color="#F45132"/>
                            </linearGradient>
                        </defs>
                        <rect width="64" height="64" rx="20" fill="url(#onboarding-gradient)"/>
                        <path d="M32 18C24.27 18 18 24.27 18 32C18 39.73 24.27 46 32 46C39.73 46 46 39.73 46 32C46 24.27 39.73 18 32 18Z" stroke="white" stroke-width="2.5" fill="none"/>
                        <path d="M32 14V32M22 26H42" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                
                <h1 class="fh-onboarding-title">{{ __('Welcome to FanHub+!') }}</h1>
                <p class="fh-onboarding-subtitle">{{ __('Choose your favorite fandoms to personalize your experience') }}</p>
            </div>

            <form action="{{ route('onboarding.store') }}" method="POST" id="onboardingForm" novalidate>
                @csrf
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-semibold mb-0">{{ __('Select Your Favorite Fandoms') }}</label>
                        <span class="fh-onboarding-counter" id="selectionCounter" role="status" aria-live="polite">
                            <span class="counter-label">{{ __('Selected') }}</span>
                            <span class="counter-value" id="selectedCount">0</span>
                            <span class="counter-divider" aria-hidden="true">/</span>
                            <span class="counter-max">5</span>
                        </div>
                    </div>
                    <p class="fh-onboarding-subtitle mb-3">{{ __('Select 3–5 fandoms to personalize your feed') }}</p>
                    
                    <div class="fh-category-pills" id="categoriesContainer" role="group" aria-label="{{ __('Select your favorite fandoms') }}">
                        @foreach ($categories as $category)
                            <label class="fh-category-pill {{ in_array($category->id, $selectedCategoryIds) ? 'selected' : '' }}" 
                                   data-category-id="{{ $category->id }}"
                                                   onclick="toggleCategory(this, {{ $category->id }})"
                                   role="checkbox"
                                                   aria-checked="{{ in_array($category->id, $selectedCategoryIds) ? 'true' : 'false' }}"
                                                   tabindex="0"
                                                   aria-label="{{ $category->name }} {{ in_array($category->id, $selectedCategoryIds) ? 'selected' : 'not selected' }}">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="sr-only" 
                                       {{ in_array($category->id, $selectedCategoryIds) ? 'checked' : '' }}
                                       aria-hidden="true">
                                
                                <span class="fh-pill-icon" aria-hidden="true">
                                    @if ($category->iconMedia && $category->iconMedia->url)
                                        <img src="{{ $category->iconMedia->url }}" alt="" loading="lazy" width="24" height="24">
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" aria-hidden="true">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-5.18 5.18a5.5 5.5 0 0 0 0 7.78L12 21.33l7.78-7.78a5.5 5.5 0 0 0 0-7.78L12 10.67l5.18-5.18a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @else
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" width="18" height="18" aria-hidden="true">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-5.18 5.18a5.5 5.5 0 0 0 0 7.78L12 21.33l7.78-7.78a5.5 5.5 0 0 0 0-7.78L12 10.67l5.18-5.18a5.5 5.5 0 0 0 0-7.78z" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    @endif
                                </span>
                                
                                <span class="fh-pill-name">{{ $category->name }}</span>
                                
                                <span class="fh-pill-check" aria-hidden="true">
                                    <svg viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    
                    @error('categories')
                        <div class="fh-onboarding-error" role="alert">
                            <i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i>
                            <span>{{ $message }}</span>
                        </div>
                    @enderror
                </div>
    
                <div class="d-grid mt-4">
                    <button type="submit" class="fh-onboarding-submit" id="submitBtn" disabled>
                        <span class="btn-text"><i class="bi bi-check-circle me-2" aria-hidden="true"></i>{{ __('Complete Setup') }}</span>
                        <span class="btn-loader" aria-hidden="true">
                            <svg class="spinner" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4 31.4" stroke-dashoffset="31.4">
                                    <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                                </circle>
                            </svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('onboardingForm');
        if (!form) return;

        const submitBtnEl = document.getElementById('submitBtn');
        const selectedCountEl = document.getElementById('selectedCount');
        const categoriesContainer = document.getElementById('categoriesContainer');
        const submitBtnEl = document.getElementById('submitBtn');

        // Initialize selection count
        updateSelectionCount();
        
        // Handle category pill clicks
        document.querySelectorAll('.fh-category-pill').forEach(pill => {
            pill.addEventListener('click', function(e) {
                // Don't toggle if clicking on the checkbox directly (it handles itself)
                if (e.target.type === 'checkbox') return;
                
                const checkbox = this.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
                toggleCategory(this, checkbox.value);
            });
            
            // Handle checkbox change
            const checkbox = pill.querySelector('input[type="checkbox"]');
            if (checkbox) {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        this.closest('.fh-category-pill').classList.add('selected');
                    } else {
                        this.closest('.fh-category-pill').classList.remove('selected');
                    }
                    updateSelectionCount();
                });
            });
            
            // Keyboard support
            pill.addEventListener('keydown', function(e) {
                if (e.key === ' ' || e.key === 'Enter') {
                    e.preventDefault();
                    this.click();
                }
            });
        });

        function toggleCategory(pill, categoryId) {
            const checkbox = pill.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        }

        function updateSelectionCount() {
            const checkedCount = document.querySelectorAll('#categoriesContainer input[type="checkbox"]:checked').length;
            selectedCountEl.textContent = checkedCount;
            
            // Enable/disable submit button
            submitBtnEl.disabled = checkedCount < 3;
            
            // Update counter text
            selectedCountEl.textContent = checkedCount;
            
            // Visual feedback on counter
            const counterEl = document.getElementById('selectionCounter');
            if (counterEl) {
                counterEl.classList.remove('ready');
                if (checkedCount >= 3 && checkedCount <= 5) {
                    counterEl.classList.add('ready');
                }
            }
            
            // Visual feedback on counter
            selectedCountEl.classList.remove('text-success', 'text-warning', 'text-danger');
            if (checkedCount >= 3 && checkedCount <= 5) {
                selectedCountEl.classList.add('text-success');
            } else if (checkedCount > 5) {
                selectedCountEl.classList.add('text-danger');
            } else {
                selectedCountEl.classList.add('text-warning');
            }
        }

        function showError(message) {
            const existingError = document.querySelector('.fh-onboarding-error');
            if (existingError) existingError.remove();
            
            const alert = document.createElement('div');
            alert.className = 'fh-onboarding-error';
            alert.innerHTML = `
                <svg viewBox="0 0 16 16" fill="currentColor" width="16" height="16" aria-hidden="true">
                    <path d="M8 16A8 8 0 108 0a8 8 0 000 16zm-3.97-6.03a.75.75 0 00-1.08.022L7 10.94l-2.07-2.07a.75.75 0 10-1.06 1.06l2.5 2.5a.75.75 0 001.06 0l2.5 2.5a.75.75 0 001.06 0l-2.5 2.5a.75.75 0 001.06 0l-2.5 2.5a.75.75 0 001.06 0l-2.5 2.5a.75.75 0 001.06 0z"/>
                </svg>
                <span>${message}</span>
            `;
            
            const form = document.getElementById('onboardingForm');
            const existingError = form.querySelector('.fh-onboarding-error');
            if (existingError) existingError.remove();
            form.insertAdjacentElement('afterbegin', alert);
        }
    });
    </script>
    @endpush