export function initMultimedia(page) {
    const section = page.querySelector('[data-home-multimedia]');
    if (!section) return;
    section.querySelectorAll('[data-media-row]').forEach((row) => {
        const track = row.querySelector('[data-media-track]');
        const group = row.querySelector('[data-media-group]');
        const originals = [...group.children];
        const clone = (element) => {
            const copy = element.cloneNode(true);
            copy.dataset.mediaClone = '';
            copy.setAttribute('aria-hidden', 'true');
            if (copy.matches('a')) copy.tabIndex = -1;
            // Strip modal triggers so cloned loop tiles do not open extra dialogs.
            copy.removeAttribute('data-fan-open');
            copy.removeAttribute('data-fan-home');
            copy.querySelectorAll('a').forEach((link) => {
                link.tabIndex = -1;
                link.removeAttribute('data-fan-open');
                link.removeAttribute('data-fan-home');
            });
            return copy;
        };
        let lastWidth = 0;
        const fill = () => {
            if (!row.clientWidth || row.clientWidth === lastWidth) return;
            lastWidth = row.clientWidth;
            row.classList.remove('is-ready');
            track.querySelectorAll('[data-media-clone]').forEach((copy) => copy.remove());
            const baseWidth = group.getBoundingClientRect().width;
            if (!baseWidth) return;
            const repeats = Math.ceil((row.clientWidth + originals[0].offsetWidth) / baseWidth);
            for (let i = 1; i < repeats; i++) originals.forEach((card) => group.append(clone(card)));
            track.append(clone(group));
            row.style.setProperty('--media-duration', `${Math.max(30, group.getBoundingClientRect().width / 38)}s`);
            row.classList.add('is-ready');
        };
        new ResizeObserver(fill).observe(row);
        fill();
    });

    const pause = section.querySelector('[data-media-pause]');
    pause?.addEventListener('click', () => {
        const paused = section.classList.toggle('is-paused');
        pause.setAttribute('aria-pressed', String(paused));
        pause.querySelector('span').textContent = paused ? 'Resume motion' : 'Pause motion';
        pause.querySelector('i').className = paused ? 'bi bi-play' : 'bi bi-pause';
    });
    new IntersectionObserver(([entry]) => {
        section.classList.toggle('is-offscreen', !entry.isIntersecting);
    }).observe(section);
}
