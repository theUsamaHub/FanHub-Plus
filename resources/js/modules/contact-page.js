/**
 * Contact Page - VIP Interactions & Animations
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
// Form Submission with Animation
// ==========================================================================

function initFormSubmission() {
    const form = document.querySelector('[data-contact-form]');
    const submitBtn = document.querySelector('[data-contact-submit]');
    if (!form || !submitBtn) return;

    form.addEventListener('submit', async (e) => {
        // Don't prevent default - let Laravel handle the form submission
        // We just add the submitting state for UX

        // Validate required fields first
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('contact-field__input--error');
            } else {
                field.classList.remove('contact-field__input--error');
            }
        });

        if (!isValid) {
            e.preventDefault();
            // Shake the first invalid field
            const firstInvalid = form.querySelector('.contact-field__input--error');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.animate([
                    { transform: 'translateX(0)' },
                    { transform: 'translateX(-8px)' },
                    { transform: 'translateX(8px)' },
                    { transform: 'translateX(-8px)' },
                    { transform: 'translateX(0)' }
                ], { duration: 400, easing: 'ease-out' });
            }
            return;
        }

        // Show submitting state
        submitBtn.dataset.submitting = 'true';
        submitBtn.disabled = true;
    });

    // Remove error state on input
    form.querySelectorAll('[required]').forEach(field => {
        field.addEventListener('input', () => {
            if (field.value.trim()) {
                field.classList.remove('contact-field__input--error');
            }
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
    const hero = document.querySelector('[data-contact-hero]');
    if (!particles || !hero) return;

    let rafId = null;
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
        rafId = requestAnimationFrame(animate);
    }

    animate();

    // Cleanup
    return () => cancelAnimationFrame(rafId);
}

// ==========================================================================
// Counter Animation for Stats
// ==========================================================================

function initCounterAnimation() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        document.querySelectorAll('[data-count]').forEach(el => {
            el.textContent = el.dataset.count;
        });
        return;
    }

    const counters = document.querySelectorAll('[data-count]');
    if (!counters.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => observer.observe(counter));
}

function animateCounter(element) {
    const target = parseInt(element.dataset.count, 10);
    const duration = 2000;
    const startTime = performance.now();

    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        // Easing: easeOutExpo
        const eased = progress === 1 ? 1 : 1 - Math.pow(2, -10 * progress);
        const current = Math.floor(eased * target);
        element.textContent = current.toLocaleString();

        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }

    requestAnimationFrame(update);
}

// ==========================================================================
// Toast Notification System
// ==========================================================================

function initToast() {
    // Check for success message in URL or session
    const urlParams = new URLSearchParams(window.location.search);
    const toast = document.getElementById('contact-toast');

    if (toast && (urlParams.get('sent') === 'true' || document.querySelector('.contact-alert--success'))) {
        showToast();
    }

    // Listen for toast close
    const closeBtn = toast?.querySelector('.contact-toast__close');
    closeBtn?.addEventListener('click', hideToast);

    function showToast() {
        toast.hidden = false;
        requestAnimationFrame(() => {
            toast.setAttribute('open', '');
        });

        // Auto hide after 5 seconds
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
// FAQ Accordion Enhancement
// ==========================================================================

function initFaqAccordion() {
    document.querySelectorAll('.contact-faq-item').forEach(item => {
        const summary = item.querySelector('summary');
        const content = item.querySelector('p');

        if (!summary || !content) return;

        // Add keyboard support
        summary.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                item.open = !item.open;
            }
        });

        // Smooth height animation
        item.addEventListener('toggle', () => {
            if (item.open) {
                content.style.maxHeight = content.scrollHeight + 'px';
                content.style.opacity = '1';
            } else {
                content.style.maxHeight = '0';
                content.style.opacity = '0';
            }
        });

        // Initial state
        content.style.maxHeight = item.open ? content.scrollHeight + 'px' : '0';
        content.style.opacity = item.open ? '1' : '0';
        content.style.transition = 'max-height 0.3s ease, opacity 0.2s ease';
        content.style.overflow = 'hidden';
    });
}

// ==========================================================================
// Method Card Hover Effects
// ==========================================================================

function initMethodCards() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) return;

    document.querySelectorAll('.contact-method').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateX(8px)';
        });

        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateX(0)';
        });
    });
}

// ==========================================================================
// Page Visibility Handler
// ==========================================================================

function initVisibilityHandler() {
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            // Pause animations when tab is not visible
            document.body.classList.add('contact-page-hidden');
        } else {
            document.body.classList.remove('contact-page-hidden');
            // Re-trigger counter if needed
            initCounterAnimation();
        }
    });
}

// ==========================================================================
// Main Initialization
// ==========================================================================

export function initContactPage() {
    const page = document.querySelector('[data-contact-page]');
    if (!page) return;

    initScrollAnimations();
    initCharCounter();
    initFormSubmission();
    initParticleParallax();
    initCounterAnimation();
    initToast();
    initFaqAccordion();
    initMethodCards();
    initVisibilityHandler();

    // Handle browser back/forward cache
    window.addEventListener('pageshow', (e) => {
        if (e.persisted) {
            initScrollAnimations();
            initCounterAnimation();
        }
    });
}

// Auto-init if not using modules
if (typeof window !== 'undefined' && !window.__CONTACT_PAGE_INITIALIZED__) {
    window.__CONTACT_PAGE_INITIALIZED__ = true;
    document.addEventListener('DOMContentLoaded', initContactPage);
}