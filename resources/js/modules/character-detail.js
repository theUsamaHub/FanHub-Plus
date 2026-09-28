// A bounded portrait tilt; touch scrolling and reduced-motion preferences stay native.
const portrait = document.querySelector('[data-character-tilt]');
if (portrait) {
    const enabled = matchMedia('(hover: hover) and (pointer: fine) and (prefers-reduced-motion: no-preference)');
    const reset = () => {
        for (const key of ['--tilt-x', '--tilt-y', '--glow-x', '--glow-y']) portrait.style.removeProperty(key);
    };
    portrait.addEventListener('pointermove', (event) => {
        if (!enabled.matches || event.pointerType === 'touch') return;
        const bounds = portrait.getBoundingClientRect();
        const x = Math.max(0, Math.min(1, (event.clientX - bounds.left) / bounds.width));
        const y = Math.max(0, Math.min(1, (event.clientY - bounds.top) / bounds.height));
        portrait.style.setProperty('--tilt-x', `${(0.5 - y) * 12}deg`);
        portrait.style.setProperty('--tilt-y', `${(x - 0.5) * 14}deg`);
        portrait.style.setProperty('--glow-x', `${x * 100}%`);
        portrait.style.setProperty('--glow-y', `${y * 100}%`);
    });
    portrait.addEventListener('pointerleave', reset);
    portrait.addEventListener('pointercancel', reset);
    enabled.addEventListener('change', reset);
}
