// Progressive enhancement: cards remain visible without JavaScript or animation support.
const cards = document.querySelectorAll('[data-card-reveal]');
const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

if (cards.length && !reducedMotion.matches && 'IntersectionObserver' in window && Element.prototype.animate) {
    const animations = new Set();
    const observer = new IntersectionObserver((entries) => {
        entries.filter((entry) => entry.isIntersecting).forEach((entry, index) => {
            observer.unobserve(entry.target);
            const animation = entry.target.animate([
                { opacity: 0, transform: 'translateY(32px) scale(.98)' },
                { opacity: 1, transform: 'translateY(0) scale(1)' },
            ], { duration: 650, delay: Math.min(index, 3) * 85, easing: 'cubic-bezier(.2,.7,.2,1)', fill: 'backwards' });
            animations.add(animation);
            animation.onfinish = () => animations.delete(animation);
        });
    }, { threshold: 0.08 });
    cards.forEach((card) => observer.observe(card));
    reducedMotion.addEventListener('change', (event) => {
        if (!event.matches) return;
        observer.disconnect();
        animations.forEach((animation) => animation.cancel());
        animations.clear();
    });
}
