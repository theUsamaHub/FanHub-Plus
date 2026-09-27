/**
 * Feedback Page - VIP Interactions & Animations
 */

// ==========================================================================
// Intersection Observer for Scroll Animations
// ==========================================================================

function initScrollAnimations() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        document.querySelectorAll('[data-animate]').forEach(el => el.classList.add('animate-in'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const delay = parseInt(entry.target.dataset.delay || '0', 10);
                setTimeout(() => {
                    entry.target.classList.add('animate-in');
                }, delay);
                observer.unobserve(entry.target);
            }
        });
    }, {
        rootMargin: '0px 0px -10% 0px',
        threshold: 0.1
    });

    document.querySelectorAll('[data-animate]').forEach(el => observer.observe(el));
}

// ==========================================================================
// Character Counter for Message Field
// ==========================================================================

function initCharCounter() {
    const textarea = document.getElementById('message');
    const counter = document.querySelector('[data-char-count]');
    if (!textarea || !counter) return;

    const updateCounter = () => {
        const length = textarea.value.length;
        counter.textContent = length.toLocaleString();
        counter.style.color = length > 4500 ? '#ef4444' : length > 4000 ? '#ffb300' : '#44d9ff';
    };

    textarea.addEventListener('input', updateCounter);
    updateCounter();
}

// ==========================================================================
// Type Selector Interaction
// ==========================================================================

function initTypeSelector() {
    const options = document.querySelectorAll('.feedback-type-option');
    const hiddenInputs = document.querySelectorAll('.feedback-type-option input[type="radio"]');

    options.forEach(option => {
        option.addEventListener('click', () => {
            const type = option.dataset.type;

            // Update hidden radio inputs
            hiddenInputs.forEach(input => {
                input.checked = input.value === type;
            });

            // Update visual state
            options.forEach(opt => {
                const isActive = opt.dataset.type === type;
                opt.classList.toggle('feedback-type-option--active', isActive);
                opt.setAttribute('aria-checked', isActive);
                opt.setAttribute('tabindex', isActive ? '0' : '-1');
            });

            // Trigger validation if previously errored
            const errorEl = document.querySelector('.feedback-field__error');
            if (errorEl) {
                errorEl.remove();
            }
        });

        // Keyboard support
        option.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                option.click();
            }
        });
    });

    // Initialize active state from hidden input
    const checkedInput = document.querySelector('.feedback-type-option input[type="radio"]:checked');
    if (checkedInput) {
        const activeOption = document.querySelector(`.feedback-type-option[data-type="${checkedInput.value}"]`);
        if (activeOption) {
            activeOption.classList.add('feedback-type-option--active');
            activeOption.setAttribute('aria-checked', 'true');
            activeOption.setAttribute('tabindex', '0');
        }
    }
}

// ==========================================================================
// Form Submission with Animation
// ==========================================================================

function initFormSubmission() {
    const form = document.querySelector('[data-feedback-form]');
    const submitBtn = document.querySelector('[data-feedback-submit]');
    if (!form || !submitBtn) return;

    form.addEventListener('submit', (e) => {
        // Validate required fields
        const typeRadio = form.querySelector('input[name="type"]:checked');
        const messageField = form.querySelector('[name="message"]');
        let isValid = true;

        if (!typeRadio) {
            isValid = false;
            // Show error on type selector
            const typeSelector = form.querySelector('.feedback-type-selector');
            if (typeSelector && !typeSelector.querySelector('.feedback-field__error')) {
                const errorEl = document.createElement('p');
                errorEl.className = 'feedback-field__error';
                errorEl.setAttribute('role', 'alert');
                errorEl.textContent = 'Please select a feedback type';
                typeSelector.appendChild(errorEl);
            }
        }

        if (!messageField?.value.trim()) {
            isValid = false;
            messageField?.classList.add('feedback-field__textarea--error');
        } else if (messageField?.value.trim().length < 10) {
            isValid = false;
            messageField?.classList.add('feedback-field__textarea--error');
            const hintEl = form.querySelector('#message-hint');
            if (hintEl && !hintEl.querySelector('.feedback-field__error')) {
                const errorEl = document.createElement('p');
                errorEl.className = 'feedback-field__error';
                errorEl.setAttribute('role', 'alert');
                errorEl.textContent = 'Message must be at least 10 characters';
                hintEl.parentNode.insertBefore(errorEl, hintEl.nextSibling);
            }
        }

        if (!isValid) {
            e.preventDefault();

            // Focus first invalid field
            const firstInvalid = form.querySelector('.feedback-field__textarea--error, .feedback-type-option--active');
            if (firstInvalid) {
                firstInvalid.focus();
            }

            // Shake animation
            const invalidFields = form.querySelectorAll('.feedback-field__textarea--error, .feedback-type-selector:has(.feedback-field__error)');
            invalidFields.forEach(field => {
                field.animate([
                    { transform: 'translateX(0)' },
                    { transform: 'translateX(-8px)' },
                    { transform: 'translateX(8px)' },
                    { transform: 'translateX(-8px)' },
                    { transform: 'translateX(0)' }
                ], { duration: 400, easing: 'ease-out' });
            });

            return;
        }

        // Show submitting state
        submitBtn.dataset.submitting = 'true';
        submitBtn.disabled = true;
    });

    // Remove error state on input
    const messageField = form.querySelector('[name="message"]');
    messageField?.addEventListener('input', () => {
        if (messageField.value.trim()) {
            messageField.classList.remove('feedback-field__textarea--error');
            const errorEl = form.querySelector('#message-error');
            errorEl?.remove();
        }
    });

    // Remove error when type selected
    document.querySelectorAll('.feedback-type-option').forEach(option => {
        option.addEventListener('click', () => {
            const errorEl = form.querySelector('.feedback-type-selector .feedback-field__error');
            errorEl?.remove();
        });
    });
}

// ==========================================================================
// Particle Mouse Parallax
// ==========================================================================

function initParticleParallax() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    const particles = document.querySelector('[data-particles]');
    const hero = document.querySelector('[data-feedback-hero]');
    if (!particles || !hero) return;

    let mouseX = 0;
    let mouseY = 0;
    let currentX = 0;
    let currentY = 0;

    hero.addEventListener('mousemove', (e) => {
        const rect = hero.getBoundingClientRect();
        mouseX = (e.clientX - rect.left) / rect.width - 0.5;
        mouseY = (e.clientY - rect.top) / rect.height - 0.5;
    });

    hero.addEventListener('mouseleave', () => {
        mouseX = 0;
        mouseY = 0;
    });

    function animate() {
        currentX += (mouseX - currentX) * 0.05;
        currentY += (mouseY - currentY) * 0.05;

        particles.style.transform = `translate(${currentX * 30}px, ${currentY * 30}px)`;
        requestAnimationFrame(animate);
    }

    animate();
}

// ==========================================================================
// Toast Notification System
// ==========================================================================

function initToast() {
    const toast = document.getElementById('feedback-toast');

    // Check for success message
    const urlParams = new URLSearchParams(window.location.search);
    if (toast && (urlParams.get('sent') === 'true' || document.querySelector('.feedback-alert--success'))) {
        showToast();
    }

    const closeBtn = toast?.querySelector('.feedback-toast__close');
    closeBtn?.addEventListener('click', hideToast);

    function showToast() {
        toast.hidden = false;
        requestAnimationFrame(() => {
            toast.setAttribute('open', '');
        });
        setTimeout(hideToast, 5000);
    }

    function hideToast() {
        toast.removeAttribute('open');
        setTimeout(() => {
            toast.hidden = true;
        }, 400);
    }
}

// ==========================================================================
// History Item Stagger Animation
// ==========================================================================

function initHistoryStagger() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    const items = document.querySelectorAll('.feedback-history-item[data-animate]');
    if (!items.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const delay = parseInt(entry.target.dataset.delay || '0', 10);
                setTimeout(() => {
                    entry.target.classList.add('animate-in');
                }, delay);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -20px 0px' });

    items.forEach(item => observer.observe(item));
}

// ==========================================================================
// Page Visibility Handler
// ==========================================================================

function initVisibilityHandler() {
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            document.body.classList.add('feedback-page-hidden');
        } else {
            document.body.classList.remove('feedback-page-hidden');
        }
    });
}

// ==========================================================================
// Main Initialization
// ==========================================================================

export function initFeedbackPage() {
    const page = document.querySelector('[data-feedback-page]');
    if (!page) return;

    initScrollAnimations();
    initCharCounter();
    initTypeSelector();
    initFormSubmission();
    initParticleParallax();
    initToast();
    initHistoryStagger();
    initVisibilityHandler();

    // Handle browser back/forward cache
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) {
            initScrollAnimations();
            initHistoryStagger();
        }
    });
}

// Auto-init if not using modules
if (typeof window !== 'undefined' && !window.__FEEDBACK_PAGE_INITIALIZED__) {
    window.__FEEDBACK_PAGE_INITIALIZED__ = true;
    document.addEventListener('DOMContentLoaded', initFeedbackPage);
}