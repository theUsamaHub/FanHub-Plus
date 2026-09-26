@extends($layout ?? 'layouts.app')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center">
    {{-- Onboarding Modal for Favorite Categories Selection --}}
    <div class="modal fade show" id="onboardingModal" tabindex="-1" aria-labelledby="onboardingModalLabel" aria-hidden="false" data-bs-backdrop="static" data-bs-keyboard="false" style="display: block;">
        <div class="modal-dialog modal-fullscreen modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <div class="text-center w-100">
                        <div class="inline-flex items-center justify-content-center w-16 h-16 rounded-full bg-primary/10 text-primary mb-4 mx-auto">
                            <i class="bi bi-heart-fill fs-2"></i>
                        </div>
                        <h2 class="h3 fw-bold mb-1">{{ __('Welcome to FanHub+!') }}</h2>
                        <p class="text-muted mb-0">{{ __('Choose your favorite fandoms to personalize your experience') }}</p>
                    </div>
                    {{-- No close button - user must complete onboarding --}}
                </div>
                <div class="modal-body pb-0">
                    <form action="{{ route('onboarding.store') }}" method="POST" id="onboardingForm">
                        @csrf
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <label class="form-label fw-semibold mb-0">{{ __('Select Your Favorite Fandoms') }}</label>
                                <span class="badge bg-primary fs-6" id="selectionCounter">{{ __('Selected') }}: <span id="selectedCount">0</span> / 5</label>
                            </div>
                            <p class="text-muted small mb-3">{{ __('Select at least 3, maximum 5 fandoms to personalize your feed.') }}</p>
                            
                            <div class="row g-3" id="categoriesContainer">
                                @foreach ($categories as $category)
                                    <div class="col-6 col-md-4 col-xl-3">
                                        <label class="category-card h-100 d-flex flex-column {{ in_array($category->id, $selectedCategoryIds) ? 'selected' : '' }}" 
                                               data-category-id="{{ $category->id }}"
                                               onclick="toggleCategory(this, {{ $category->id }})">
                                            <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="d-none" 
                                                   {{ in_array($category->id, $selectedCategoryIds) ? 'checked' : '' }}>
                                            <div class="category-icon mb-2">
                                                @if ($category->iconMedia && $category->iconMedia->url)
                                                    <img src="{{ $category->iconMedia->url }}" alt="{{ $category->name }}" class="img-fluid rounded" style="width: 48px; height: 48px; object-fit: cover;">
                                                @else
                                                    <div class="d-inline-flex align-items-center justify-content-center bg-primary/10 text-primary rounded" style="width: 48px; height: 48px;">
                                                        <i class="bi bi-tag fs-4"></i>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="category-name text-center fw-medium text-truncate">{{ $category->name }}</div>
                                            @if ($category->description)
                                                <div class="category-description text-muted small text-center mt-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $category->description }}</div>
                                            @endif
                                            <div class="category-check mt-auto">
                                                <div class="check-indicator d-inline-flex align-items-center justify-content-center">
                                                    <i class="bi bi-check-lg fs-4 text-primary d-none"></i>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            
                            @error('categories')
                                <div class="alert alert-danger mt-3 py-2 small mb-0">
                                    <i class="bi bi-exclamation-triangle me-1"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                                <i class="bi bi-check-circle me-2"></i> {{ __('Complete Setup') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('onboardingModal');
    const form = document.getElementById('onboardingForm');
    const submitBtn = document.getElementById('submitBtn');
    const selectedCountEl = document.getElementById('selectedCount');
    const categoriesContainer = document.getElementById('categoriesContainer');
    const submitBtnEl = document.getElementById('submitBtn');
    const cancelBtn = document.getElementById('cancelUpload');
    
    let uploadXhr = null;
    let isChunkedUpload = false;
    let uploadMediaId = null;
    let totalChunks = 0;
    let uploadedChunks = 0;
    let chunkSize = 5 * 1024 * 1024; // 5MB default chunk size
    let currentFile = null;
    let currentChunkIndex = 0;

    // Initialize selection count
    updateSelectionCount();
    
    // Handle category card clicks
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Don't toggle if clicking on the checkbox directly (it handles itself)
            if (e.target.type === 'checkbox') return;
            
            const checkbox = this.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            toggleCategory(this, checkbox.value);
        });
        
        // Handle checkbox change
        const checkbox = card.querySelector('input[type="checkbox"]');
        if (checkbox) {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    this.closest('.category-card').classList.add('selected');
                } else {
                    this.closest('.category-card').classList.remove('selected');
                }
                updateSelectionCount();
            });
        }
    });

    function toggleCategory(card, categoryId) {
        const checkbox = card.querySelector('input[type="checkbox"]');
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
        selectedCountEl.classList.remove('text-success', 'text-warning', 'text-danger');
        if (checkedCount >= 3 && checkedCount <= 5) {
            selectedCountEl.classList.add('text-success');
        } else if (checkedCount > 5) {
            selectedCountEl.classList.add('text-danger');
        } else {
            selectedCountEl.classList.add('text-warning');
        }
    }

    // Form submission
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const checkedCount = document.querySelectorAll('#categoriesContainer input[type="checkbox"]:checked').length;
            
            if (checkedCount < 3) {
                showError('Please select at least 3 favorite categories.');
                return;
            }
            
            if (checkedCount > 5) {
                showError('You can select a maximum of 5 categories.');
                return;
            }
            
            // Show loading state
            submitBtnEl.disabled = true;
            submitBtnEl.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Saving...';
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('{{ route('onboarding.store') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin'
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    // Show success and redirect
                    showSuccess('Welcome to FanHub+! Your favorite fandoms have been saved.');
                    setTimeout(() => {
                        window.location.href = '{{ route('dashboard', absolute: false) }}';
                    }, 1000);
                } else {
                    throw new Error(data.message || 'Failed to save preferences');
                }
            } catch (error) {
                showError(error.message || 'Failed to save preferences. Please try again.');
                submitBtnEl.disabled = false;
                submitBtnEl.innerHTML = '<i class="bi bi-check-circle me-2"></i> Complete Setup';
            }
        });
    }

    function updateSelectionCount() {
        const checkedCount = document.querySelectorAll('#categoriesContainer input[type="checkbox"]:checked').length;
        selectedCountEl.textContent = checkedCount;
        
        // Enable/disable submit button
        submitBtnEl.disabled = checkedCount < 3;
        
        // Update counter text
        selectedCountEl.textContent = checkedCount;
        
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
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show mt-3';
        alert.innerHTML = `${message} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        form.insertAdjacentElement('afterbegin', alert);
        // hideProgress(); // Not using progress for this form
    }
    
    function showSuccess(message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show mt-3';
        alert.innerHTML = `${message} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
        form.insertAdjacentElement('afterbegin', alert);
    }
});
</script>
@endpush