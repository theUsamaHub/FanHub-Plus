import { gsap } from 'gsap';

export function initExploreFandoms(page) {
    const section = page.querySelector('[data-explore-fandoms]');
    const cluster = section?.querySelector('[data-fandom-cluster]');
    if (!cluster) return;
    const originals = [...cluster.querySelectorAll('[data-fandom-item]')];
    if (!originals.length) return;
    const playback = section.querySelector('[data-fandom-playback]');
    const center = Math.floor((originals.length - 1) / 2);
    originals[center].classList.add('is-primary');

    const media = gsap.matchMedia();
    media.add({ reduced: '(prefers-reduced-motion: reduce)', motion: '(prefers-reduced-motion: no-preference)' }, (context) => {
        if (context.conditions.reduced) {
            section.classList.add('is-static');
            return () => section.classList.remove('is-static');
        }

        let expanded = false;
        let ready = false;
        let inView = false;
        let paused = false;
        let keyboardFocused = false;
        let selected = null;
        let items = [...originals];
        let setters = [];
        let step = 0;
        let span = 0;
        let offset = 0;
        let layoutTween;
        const finePointer = () => matchMedia('(hover: hover) and (pointer: fine)').matches;
        const clearCopies = () => {
            cluster.querySelectorAll('[data-fandom-clone]').forEach((copy) => copy.remove());
            items = [...originals];
        };
        const paint = () => {
            const wrap = gsap.utils.wrap(-span / 2, span / 2);
            setters.forEach((set, index) => set(wrap((index - (items.length - 1) / 2) * step - offset)));
        };
        const layout = (animate = false) => {
            ready = false;
            layoutTween?.kill();
            clearCopies();
            const width = originals[0].offsetWidth;
            if (!expanded) {
                const stackStep = width * (innerWidth <= 700 ? .35 : .26);
                layoutTween = gsap.to(originals, {
                    x: (index) => (index - (originals.length - 1) / 2) * stackStep,
                    scale: (index) => index === center ? 1.15 : Math.max(.82, .98 - Math.abs(index - center) * .045),
                    zIndex: (index) => originals.length - Math.abs(index - center),
                    duration: animate ? .55 : 0, ease: 'power3.out',
                });
                return;
            }
            step = width + (innerWidth <= 700 ? 18 : 24);
            // Copies fill the visual belt while the database links remain the
            // single keyboard and screen-reader sequence.
            const sets = Math.max(1, Math.ceil((cluster.clientWidth + step * 2) / (originals.length * step)));
            for (let set = 1; set < sets; set++) originals.forEach((item) => {
                const copy = item.cloneNode(true);
                copy.dataset.fandomClone = '';
                copy.classList.remove('is-primary', 'is-selected');
                copy.setAttribute('aria-hidden', 'true');
                copy.tabIndex = -1;
                cluster.append(copy);
                items.push(copy);
            });
            span = items.length * step;
            offset = 0;
            setters = items.map((item) => gsap.quickSetter(item, 'x', 'px'));
            layoutTween = gsap.to(items, {
                x: (index) => (index - (items.length - 1) / 2) * step,
                scale: 1, zIndex: 1, duration: animate ? .55 : 0, ease: 'power3.out',
                onComplete: () => { ready = true; },
            });
        };
        const expand = () => {
            if (expanded) return;
            expanded = true;
            section.classList.add('is-expanded');
            playback.hidden = originals.length < 2;
            layout(true);
        };
        const tick = (_time, delta) => {
            if (!ready || !inView || document.hidden || paused || keyboardFocused || selected || originals.length < 2) return;
            offset = (offset + Math.min(delta, 50) * .028) % span;
            paint();
        };
        const enter = () => { if (finePointer()) expand(); };
        const select = (item) => {
            items.forEach((entry) => entry.classList.toggle('is-selected', entry === item));
            selected = item;
        };
        const collapse = () => {
            if (!expanded) return;
            expanded = false;
            paused = false;
            select(null);
            section.classList.remove('is-expanded');
            playback.hidden = true;
            playback.setAttribute('aria-pressed', 'false');
            playback.textContent = 'Pause motion';
            layout(true);
        };
        const leave = () => { if (finePointer() && !keyboardFocused) collapse(); };
        const click = (event) => {
            const item = event.target.closest('[data-fandom-item]');
            if (!item || finePointer()) return;
            if (!expanded || selected !== item) {
                event.preventDefault();
                expand();
                select(item);
            }
        };
        const focusIn = (event) => {
            if (!event.target.matches('[data-fandom-item]:focus-visible')) return;
            expand();
            layoutTween?.progress(1);
            keyboardFocused = true;
            offset = (items.indexOf(event.target) - (items.length - 1) / 2) * step;
            paint();
        };
        const focusOut = (event) => {
            keyboardFocused = cluster.contains(event.relatedTarget);
            if (!keyboardFocused && !section.matches(':hover')) collapse();
        };
        const toggle = () => {
            paused = !paused;
            select(null);
            playback.setAttribute('aria-pressed', String(paused));
            playback.textContent = paused ? 'Resume motion' : 'Pause motion';
        };
        const outside = (event) => { if (!section.contains(event.target)) collapse(); };
        let previousWidth = 0;
        const resize = new ResizeObserver(() => {
            const width = cluster.clientWidth;
            if (width === previousWidth) return;
            previousWidth = width;
            select(null);
            layout();
        });
        const visibility = new IntersectionObserver(([entry]) => { inView = entry.isIntersecting; });
        resize.observe(cluster);
        visibility.observe(section);
        layout();
        gsap.ticker.add(tick);
        section.addEventListener('pointerenter', enter);
        section.addEventListener('pointerleave', leave);
        cluster.addEventListener('click', click);
        cluster.addEventListener('focusin', focusIn);
        cluster.addEventListener('focusout', focusOut);
        playback.addEventListener('click', toggle);
        document.addEventListener('pointerdown', outside);

        return () => {
            layoutTween?.kill();
            gsap.ticker.remove(tick);
            resize.disconnect();
            visibility.disconnect();
            section.removeEventListener('pointerenter', enter);
            section.removeEventListener('pointerleave', leave);
            cluster.removeEventListener('click', click);
            cluster.removeEventListener('focusin', focusIn);
            cluster.removeEventListener('focusout', focusOut);
            playback.removeEventListener('click', toggle);
            document.removeEventListener('pointerdown', outside);
            clearCopies();
            originals.forEach((item) => item.classList.remove('is-selected'));
            gsap.set(originals, { clearProps: 'transform,zIndex' });
            section.classList.remove('is-expanded');
            playback.hidden = true;
            playback.setAttribute('aria-pressed', 'false');
            playback.textContent = 'Pause motion';
        };
    });
    return () => media.revert();
}
