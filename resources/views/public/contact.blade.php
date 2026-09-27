@extends('layouts.public')
@section('title', 'Contact Us | Fan Hub Plus')
@section('main-class', 'contact-page')
@push('styles')
    @vite('resources/css/pages/contact.css')
@endpush
@section('content')
<div class="contact-page" data-contact-page>
    <!-- Hero Section -->
    <section class="contact-hero" data-contact-hero>
        <div class="contact-hero__bg" aria-hidden="true">
            <div class="contact-hero__gradient"></div>
            <div class="contact-hero__particles" data-particles></div>
        </div>
        <div class="contact-hero__content">
            <p class="contact-kicker" data-animate="fade-up" data-delay="0">GET IN TOUCH</p>
            <h1 data-animate="fade-up" data-delay="100">Let's start a <span class="contact-highlight">conversation.</span></h1>
            <p class="contact-lead" data-animate="fade-up" data-delay="200">Have a question, suggestion, or just want to say hello? We'd love to hear from you. Our team reads every message personally.</p>
            <div class="contact-hero__stats" data-animate="fade-up" data-delay="300" role="list" aria-label="Contact statistics">
                <div class="contact-stat" role="listitem">
                    <span class="contact-stat__value" data-count="24">0</span>
                    <span class="contact-stat__label">Hour Response</span>
                </div>
                <div class="contact-stat" role="listitem">
                    <span class="contact-stat__value" data-count="98">0</span>
                    <span class="contact-stat__label">% Satisfaction</span>
                </div>
                <div class="contact-stat" role="listitem">
                    <span class="contact-stat__value" data-count="500">0</span>
                    <span class="contact-stat__label">Messages/Month</span>
                </div>
            </div>
        </div>
        <div class="contact-hero__scroll" data-animate="fade-up" data-delay="500" aria-hidden="true">
            <x-site-icon name="arrow" class="contact-scroll-icon" />
            <span>Scroll</span>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-form-section" data-contact-form-section>
        <div class="contact-container">
            <div class="contact-grid">
                <!-- Form Side -->
                <div class="contact-form-wrapper" data-contact-form-wrapper>
                    <div class="contact-form-card" data-animate="slide-up" data-delay="100">
                        <div class="contact-form-header">
                            <p class="contact-form-kicker">SEND A MESSAGE</p>
                            <h2>We're all ears</h2>
                            <p>Fill out the form and we'll get back to you within 24 hours.</p>
                        </div>

                        @if (session('success'))
                            <div class="contact-alert contact-alert--success" data-animate="scale-in" role="alert">
                                <x-site-icon name="check" class="contact-alert-icon" />
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="contact-alert contact-alert--error" data-animate="scale-in" role="alert">
                                <x-site-icon name="close" class="contact-alert-icon" />
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        <form action="{{ route('contact.store') }}" method="POST" class="contact-form" data-contact-form>
                            @csrf

                            <div class="contact-form-row">
                                <div class="contact-field" data-animate="fade-up" data-delay="100">
                                    <label for="name" class="contact-field__label">
                                        <span>Your Name</span>
                                        <x-site-icon name="user" class="contact-field-icon" aria-hidden="true" />
                                    </label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="name"
                                        class="contact-field__input {{ $errors->has('name') ? 'contact-field__input--error' : '' }}"
                                        value="{{ old('name') }}"
                                        required
                                        maxlength="255"
                                        autocomplete="name"
                                        placeholder="Enter your name"
                                        aria-describedby="{{ $errors->has('name') ? 'name-error' : 'name-hint' }}"
                                    >
                                    @error('name')
                                        <p id="name-error" class="contact-field__error" role="alert">{{ $message }}</p>
                                    @enderror
                                    <span id="name-hint" class="contact-field__hint">How should we address you?</span>
                                </div>

                                <div class="contact-field" data-animate="fade-up" data-delay="150">
                                    <label for="email" class="contact-field__label">
                                        <span>Email Address</span>
                                        <x-site-icon name="mail" class="contact-field-icon" aria-hidden="true" />
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        name="email"
                                        class="contact-field__input {{ $errors->has('email') ? 'contact-field__input--error' : '' }}"
                                        value="{{ old('email') }}"
                                        required
                                        maxlength="255"
                                        autocomplete="email"
                                        placeholder="you@example.com"
                                        aria-describedby="{{ $errors->has('email') ? 'email-error' : 'email-hint' }}"
                                    >
                                    @error('email')
                                        <p id="email-error" class="contact-field__error" role="alert">{{ $message }}</p>
                                    @enderror
                                    <span id="email-hint" class="contact-field__hint">We'll reply here</span>
                                </div>
                            </div>

                            <div class="contact-field" data-animate="fade-up" data-delay="200">
                                <label for="subject" class="contact-field__label">
                                    <span>Subject</span>
                                    <x-site-icon name="tag" class="contact-field-icon" aria-hidden="true" />
                                </label>
                                <select
                                    id="subject"
                                    name="subject"
                                    class="contact-field__select {{ $errors->has('subject') ? 'contact-field__select--error' : '' }}"
                                    aria-describedby="{{ $errors->has('subject') ? 'subject-error' : 'subject-hint' }}"
                                >
                                    <option value="" disabled {{ !old('subject') ? 'selected' : '' }}>What's this about?</option>
                                    <option value="general" {{ old('subject') === 'general' ? 'selected' : '' }}>General Inquiry</option>
                                    <option value="partnership" {{ old('subject') === 'partnership' ? 'selected' : '' }}>Partnership & Business</option>
                                    <option value="press" {{ old('subject') === 'press' ? 'selected' : '' }}>Press & Media</option>
                                    <option value="technical" {{ old('subject') === 'technical' ? 'selected' : '' }}>Technical Support</option>
                                    <option value="feedback" {{ old('subject') === 'feedback' ? 'selected' : '' }}>Feedback & Suggestions</option>
                                    <option value="other" {{ old('subject') === 'other' ? 'selected' : '' }}>Something Else</option>
                                </select>
                                @error('subject')
                                    <p id="subject-error" class="contact-field__error" role="alert">{{ $message }}</p>
                                @enderror
                                <span id="subject-hint" class="contact-field__hint">Helps us route your message faster</span>
                            </div>

                            <div class="contact-field" data-animate="fade-up" data-delay="250">
                                <label for="message" class="contact-field__label">
                                    <span>Your Message</span>
                                    <x-site-icon name="message-square" class="contact-field-icon" aria-hidden="true" />
                                </label>
                                <textarea
                                    id="message"
                                    name="message"
                                    class="contact-field__textarea {{ $errors->has('message') ? 'contact-field__textarea--error' : '' }}"
                                    required
                                    maxlength="5000"
                                    minlength="10"
                                    rows="6"
                                    placeholder="Tell us what's on your mind..."
                                    aria-describedby="{{ $errors->has('message') ? 'message-error' : 'message-hint' }}"
                                >{{ old('message') }}</textarea>
                                @error('message')
                                    <p id="message-error" class="contact-field__error" role="alert">{{ $message }}</p>
                                @enderror
                                <div class="contact-field__footer">
                                    <span id="message-hint" class="contact-field__hint">
                                        <span data-char-count>0</span>/5000 characters
                                    </span>
                                    <span class="contact-field__hint">Min 10 characters</span>
                                </div>
                            </div>

                            <!-- Honeypot for spam protection -->
                            <div class="contact-honeypot" aria-hidden="true">
                                <label for="website">Don't fill this out if you're human</label>
                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <button
                                type="submit"
                                class="contact-submit"
                                data-contact-submit
                                data-animate="fade-up"
                                data-delay="300"
                            >
                                <span class="contact-submit__text">
                                    <span class="contact-submit__label">Send Message</span>
                                    <span class="contact-submit__sending" hidden>Sending...</span>
                                </span>
                                <span class="contact-submit__icon" aria-hidden="true">
                                    <x-site-icon name="send" />
                                </span>
                                <span class="contact-submit__ripple"></span>
                                <span class="contact-submit__progress" aria-hidden="true"></span>
                            </button>

                            <p class="contact-form__privacy" data-animate="fade-up" data-delay="400">
                                <x-site-icon name="shield" aria-hidden="true" />
                                By submitting, you agree to our <a href="{{ route('discover.privacy') }}">Privacy Policy</a>. We never share your data.
                            </p>
                        </form>
                    </div>
                </div>

                <!-- Info Side -->
                <div class="contact-info-wrapper" data-contact-info-wrapper>
                    <div class="contact-info-card" data-animate="slide-up" data-delay="200">
                        <div class="contact-info-header">
                            <p class="contact-info-kicker">OTHER WAYS TO REACH US</p>
                            <h2>Connect directly</h2>
                        </div>

                        <div class="contact-methods">
                            <a href="mailto:hello@fanhubplus.com" class="contact-method" data-animate="fade-right" data-delay="100">
                                <div class="contact-method__icon">
                                    <x-site-icon name="mail" />
                                </div>
                                <div class="contact-method__content">
                                    <h3>Email Us</h3>
                                    <p>hello@fanhubplus.com</p>
                                    <span class="contact-method__meta">Replies within 24h</span>
                                </div>
                                <x-site-icon name="arrow-right" class="contact-method__arrow" aria-hidden="true" />
                            </a>

                            <a href="https://discord.gg/fanhubplus" target="_blank" rel="noopener noreferrer" class="contact-method" data-animate="fade-right" data-delay="150">
                                <div class="contact-method__icon contact-method__icon--discord">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.675 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.078.078 0 0 0 .023.04 20.29 20.29 0 0 0 3.2 3.818.077.077 0 0 0 .055.02h.005a.075.075 0 0 0 .043-.018 14.876 14.876 0 0 0 3.874-1.243.076.076 0 0 0 .013-.064c-.297-.67-.538-1.43-.667-2.076a.074.074 0 0 1 .006-.064 12.6 12.6 0 0 1 1.319-2.122.076.076 0 0 1 .065-.011c.538 0 1.016.147 1.409.271.413-.118.84-.286 1.236-.286.394 0 .82.17 1.233.286.39-.124.87-.272 1.408-.272a.077.077 0 0 1 .065.011 12.737 12.737 0 0 1 1.319 2.122.077.077 0 0 1 .006.065c-.133.658-.374 1.417-.671 2.076a.077.077 0 0 0 .012.064 14.726 14.726 0 0 0 3.875 1.243.075.075 0 0 0 .044.018.075.075 0 0 0 .039-.012h.005a.076.076 0 0 0 .055-.02 20.415 20.415 0 0 0 3.207-3.818.077.077 0 0 0 .023-.04A20.177 20.177 0 0 0 20.317 4.37a.068.068 0 0 0-.032-.027zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333 1.007-2.418 2.157-2.418 1.16 0 2.157 1.096 2.157 2.418 0 1.344-.997 2.419-2.157 2.419zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.997-2.418 2.157-2.418 1.15 0 2.146 1.096 2.146 2.418 0 1.344-.996 2.419-2.146 2.419z"/></svg>
                                </div>
                                <div class="contact-method__content">
                                    <h3>Discord Community</h3>
                                    <p>Join the conversation</p>
                                    <span class="contact-method__meta">Active 24/7</span>
                                </div>
                                <x-site-icon name="arrow-right" class="contact-method__arrow" aria-hidden="true" />
                            </a>

                            <a href="https://twitter.com/fanhubplus" target="_blank" rel="noopener noreferrer" class="contact-method" data-animate="fade-right" data-delay="200">
                                <div class="contact-method__icon contact-method__icon--twitter">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 9.24-3.298 3.263-8.5-9.24-7.227 8.26h-3.307l7.228-8.26-8.5-9.24 3.297-3.263 8.5 9.24 7.227-8.26z"/></svg>
                                </div>
                                <div class="contact-method__content">
                                    <h3>Twitter / X</h3>
                                    <p>@fanhubplus</p>
                                    <span class="contact-method__meta">Quick questions</span>
                                </div>
                                <x-site-icon name="arrow-right" class="contact-method__arrow" aria-hidden="true" />
                            </a>

                            <div class="contact-method" data-animate="fade-right" data-delay="250">
                                <div class="contact-method__icon contact-method__icon--location">
                                    <x-site-icon name="map-pin" />
                                </div>
                                <div class="contact-method__content">
                                    <h3>Office</h3>
                                    <p>FanHub Plus HQ</p>
                                    <span class="contact-method__meta">Tokyo, Japan</span>
                                </div>
                                <x-site-icon name="arrow-right" class="contact-method__arrow" aria-hidden="true" />
                            </div>
                        </div>

                        <div class="contact-hours" data-animate="fade-up" data-delay="300">
                            <h3>Business Hours</h3>
                            <div class="contact-hours__grid">
                                <div class="contact-hours__day">
                                    <span>Mon - Fri</span>
                                    <span>9:00 - 18:00 JST</span>
                                </div>
                                <div class="contact-hours__day">
                                    <span>Saturday</span>
                                    <span>10:00 - 15:00 JST</span>
                                </div>
                                <div class="contact-hours__day">
                                    <span>Sunday</span>
                                    <span>Closed</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Quick Links -->
                    <div class="contact-faq-card" data-animate="slide-up" data-delay="300">
                        <h3>Quick Answers</h3>
                        <div class="contact-faq-list">
                            <details class="contact-faq-item">
                                <summary>How do I report a bug?</summary>
                                <p>Use the form above and select "Technical Support" as the subject. Include your browser, device, and steps to reproduce.</p>
                            </details>
                            <details class="contact-faq-item">
                                <summary>Can I suggest a new feature?</summary>
                                <p>Absolutely! Select "Feedback & Suggestions" - we read every idea and many features come from community requests.</p>
                            </details>
                            <details class="contact-faq-item">
                                <summary>Business or partnership inquiries?</summary>
                                <p>Select "Partnership & Business" and our partnerships team will review within 2 business days.</p>
                            </details>
                            <details class="contact-faq-item">
                                <summary>Press and media requests</summary>
                                <p>Select "Press & Media" for press kits, interview requests, and media assets.</p>
                            </details>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Success Toast -->
    <div class="contact-toast" id="contact-toast" role="status" aria-live="polite" hidden>
        <div class="contact-toast__content">
            <x-site-icon name="check-circle" class="contact-toast__icon" />
            <span class="contact-toast__message">Message sent successfully!</span>
        </div>
        <button class="contact-toast__close" aria-label="Dismiss">
            <x-site-icon name="close" />
        </button>
        <div class="contact-toast__progress"></div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/modules/contact-page.js')
@endpush