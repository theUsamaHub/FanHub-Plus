@extends('user.layout', ['pageTitle' => 'Feedback'])
@section('main-class', 'feedback-page')
@push('styles')
    @vite('resources/css/pages/feedback.css')
@endpush
@section('member-content')
<div class="feedback-page" data-feedback-page>
    <!-- Hero Section -->
    <section class="feedback-hero" data-feedback-hero>
        <div class="feedback-hero__bg" aria-hidden="true">
            <div class="feedback-hero__gradient"></div>
            <div class="feedback-hero__particles" data-particles></div>
        </div>
        <div class="feedback-hero__content">
            <p class="feedback-kicker" data-animate="fade-up" data-delay="0">WE'RE LISTENING</p>
            <h1 data-animate="fade-up" data-delay="100">Help shape your <span class="feedback-highlight">hub.</span></h1>
            <p class="feedback-lead" data-animate="fade-up" data-delay="200">Something broken? An idea to share? A question? Every message goes straight to our team. We read everything personally.</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="feedback-main">
        <div class="feedback-container">
            <div class="feedback-grid">
                <!-- Form Side -->
                <div class="feedback-form-wrapper" data-feedback-form-wrapper>
                    <div class="feedback-form-card" data-animate="slide-up" data-delay="100">
                        <div class="feedback-form-header">
                            <p class="feedback-form-kicker">SEND FEEDBACK</p>
                            <h2>What's on your mind?</h2>
                            <p>Choose a category and tell us more. We'll get back to you if needed.</p>
                        </div>

                        @if (session('success'))
                            <div class="feedback-alert feedback-alert--success" data-animate="scale-in" role="alert">
                                <x-site-icon name="check" class="feedback-alert-icon" />
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="feedback-alert feedback-alert--error" data-animate="scale-in" role="alert">
                                <x-site-icon name="close" class="feedback-alert-icon" />
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        <form action="{{ route('user.feedback.store') }}" method="POST" class="feedback-form" data-feedback-form>
                            @csrf

                            <!-- Type Selector -->
                            <div class="feedback-type-selector" data-animate="fade-up" data-delay="100">
                                <label class="feedback-type-label">What type of feedback?</label>
                                <div class="feedback-type-options" role="radiogroup" aria-label="Feedback type">
                                    @foreach([
                                        'suggestion' => ['An idea or suggestion', 'lightbulb', 'Share a feature request or improvement idea'],
                                        'bug' => ['Report a bug', 'bug', 'Something isn\'t working as expected'],
                                        'query' => ['Ask a question', 'help-circle', 'Need help or have a question']
                                    ] as $value => [$label, $icon, $description])
                                        <button
                                            type="button"
                                            class="feedback-type-option {{ old('type') === $value ? 'feedback-type-option--active' : '' }}"
                                            data-type="{{ $value }}"
                                            role="radio"
                                            aria-checked="{{ old('type') === $value ? 'true' : 'false' }}"
                                            aria-label="{{ $label }}"
                                            tabindex="{{ old('type') === $value ? '0' : '-1' }}"
                                        >
                                            <input type="radio" name="type" value="{{ $value }}" {{ old('type') === $value ? 'checked' : '' }} hidden aria-hidden="true">
                                            <span class="feedback-type-option__icon">
                                                <x-site-icon name="{{ $icon }}" />
                                            </span>
                                            <span class="feedback-type-option__label">{{ $label }}</span>
                                            <span class="feedback-type-option__desc">{{ $description }}</span>
                                            <span class="feedback-type-option__ring"></span>
                                        </button>
                                    @endforeach
                                </div>
                                @error('type')
                                    <p class="feedback-field__error" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Message Field -->
                            <div class="feedback-field" data-animate="fade-up" data-delay="150">
                                <label for="message" class="feedback-field__label">
                                    <span>Your Message</span>
                                    <x-site-icon name="message-square" class="feedback-field-icon" aria-hidden="true" />
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    class="feedback-field__textarea {{ $errors->has('message') ? 'feedback-field__textarea--error' : '' }}"
                                    required
                                    maxlength="5000"
                                    minlength="10"
                                    rows="7"
                                    placeholder="Tell us more... The more details, the better we can help."
                                    aria-describedby="{{ $errors->has('message') ? 'message-error' : 'message-hint' }}"
                                >{{ old('message') }}</textarea>
                                @error('message')
                                    <p id="message-error" class="feedback-field__error" role="alert">{{ $message }}</p>
                                @enderror
                                <div class="feedback-field__footer">
                                    <span id="message-hint" class="feedback-field__hint">
                                        <span data-char-count>0</span>/5000 characters
                                    </span>
                                    <span class="feedback-field__hint">Min 10 characters</span>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                class="feedback-submit"
                                data-feedback-submit
                                data-animate="fade-up"
                                data-delay="200"
                            >
                                <span class="feedback-submit__text">
                                    <span class="feedback-submit__label">Send Feedback</span>
                                    <span class="feedback-submit__sending" hidden>Sending...</span>
                                </span>
                                <span class="feedback-submit__icon" aria-hidden="true">
                                    <x-site-icon name="send" />
                                </span>
                                <span class="feedback-submit__ripple"></span>
                                <span class="feedback-submit__progress" aria-hidden="true"></span>
                            </button>

                            <p class="feedback-form__privacy" data-animate="fade-up" data-delay="300">
                                <x-site-icon name="shield" aria-hidden="true" />
                                Your feedback is private. We never share your data. <a href="{{ route('public.section', 'privacy') }}">Privacy Policy</a>
                            </p>
                        </form>
                    </div>
                </div>

                <!-- History Side -->
                <div class="feedback-history-wrapper" data-feedback-history-wrapper>
                    <div class="feedback-history-card" data-animate="slide-up" data-delay="200">
                        <div class="feedback-history-header">
                            <h2>Your Feedback History</h2>
                            <p class="feedback-history-count">{{ $feedback->total() }} submission{{ $feedback->total() !== 1 ? 's' : '' }}</p>
                        </div>

                        <div class="feedback-history-list">
                            @forelse($feedback as $item)
                                <article class="feedback-history-item" data-animate="fade-up" data-delay="{{ $loop->iteration * 50 }}">
                                    <div class="feedback-history-item__meta">
                                        <span class="feedback-status feedback-status--{{ $item->status }}">
                                            <span class="feedback-status__dot"></span>
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                        <span class="feedback-type-badge feedback-type-badge--{{ $item->type }}">
                                            <x-site-icon name="{{ $item->type === 'bug' ? 'bug' : ($item->type === 'suggestion' ? 'lightbulb' : 'help-circle') }}" />
                                            {{ ucfirst($item->type) }}
                                        </span>
                                    </div>
                                    <p class="feedback-history-item__message">{{ $item->message }}</p>
                                    <time class="feedback-history-item__date" datetime="{{ $item->created_at->toIso8601String() }}">
                                        <x-site-icon name="calendar" aria-hidden="true" />
                                        {{ $item->created_at->format('M j, Y \a\t g:i A') }}
                                    </time>
                                </article>
                            @empty
                                <div class="feedback-empty" data-animate="fade-up" data-delay="100">
                                    <div class="feedback-empty__icon">
                                        <x-site-icon name="mail" />
                                    </div>
                                    <h3>No feedback yet</h3>
                                    <p>Your submissions and their status will appear here.</p>
                                    <span class="feedback-empty__hint">We respond to every message personally</span>
                                </div>
                            @endforelse
                        </div>

                        @if($feedback->hasPages())
                            <div class="feedback-pagination">
                                {{ $feedback->links() }}
                            </div>
                        @endif
                    </div>

                    <!-- Status Legend -->
                    <div class="feedback-legend-card" data-animate="slide-up" data-delay="300">
                        <h3>Status Meanings</h3>
                        <div class="feedback-legend">
                            <div class="feedback-legend-item">
                                <span class="feedback-status feedback-status--open">
                                    <span class="feedback-status__dot"></span>
                                    Open
                                </span>
                                <span>Received and queued for review</span>
                            </div>
                            <div class="feedback-legend-item">
                                <span class="feedback-status feedback-status--in_review">
                                    <span class="feedback-status__dot"></span>
                                    In Review
                                </span>
                                <span>Our team is looking into it</span>
                            </div>
                            <div class="feedback-legend-item">
                                <span class="feedback-status feedback-status--resolved">
                                    <span class="feedback-status__dot"></span>
                                    Resolved
                                </span>
                                <span>Issue fixed or suggestion implemented</span>
                            </div>
                            <div class="feedback-legend-item">
                                <span class="feedback-status feedback-status--closed">
                                    <span class="feedback-status__dot"></span>
                                    Closed
                                </span>
                                <span>No further action needed</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Success Toast -->
    <div class="feedback-toast" id="feedback-toast" role="status" aria-live="polite" hidden>
        <div class="feedback-toast__content">
            <x-site-icon name="check-circle" class="feedback-toast__icon" />
            <span class="feedback-toast__message">Feedback sent successfully!</span>
        </div>
        <button class="feedback-toast__close" aria-label="Dismiss">
            <x-site-icon name="close" />
        </button>
        <div class="feedback-toast__progress"></div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/modules/feedback-page.js')
@endpush