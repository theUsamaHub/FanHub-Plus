import '../../css/pages/discovery.css';

// Everything remains visible and usable without motion or JavaScript.
const root = document.querySelector('[data-discovery]');
if (root) {
    const preference = matchMedia('(prefers-reduced-motion: reduce)');
    const animations = new Set();
    let observer;
    const stop = () => { observer?.disconnect(); animations.forEach(animation => animation.cancel()); animations.clear(); };
    const start = () => {
        stop();
        if (preference.matches || !('IntersectionObserver' in window) || !Element.prototype.animate) return;
        observer = new IntersectionObserver(entries => {
            entries.filter(entry => entry.isIntersecting).forEach((entry, index) => {
                observer.unobserve(entry.target);
                const animation = entry.target.animate([{ opacity: 0, transform: 'translateY(22px)' }, { opacity: 1, transform: 'translateY(0)' }],
                    { duration: 700, delay: Math.min(index, 2) * 90, easing: 'cubic-bezier(.16,1,.3,1)', fill: 'backwards' });
                animations.add(animation);
                animation.onfinish = () => animations.delete(animation);
            });
        }, { threshold: .1 });
        root.querySelectorAll('[data-discovery-reveal]').forEach(element => observer.observe(element));
    };
    start();
    preference.addEventListener('change', start);
    window.addEventListener('pagehide', stop);
    window.addEventListener('pageshow', event => { if (event.persisted) start(); });
}
