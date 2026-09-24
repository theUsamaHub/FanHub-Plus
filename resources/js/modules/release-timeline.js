export function initReleaseTimeline(page) {
    const section = page.querySelector('[data-upcoming-section]');
    if (!section) return;
    const filters = section.querySelector('[data-release-filters]');
    const previous = section.querySelector('[data-release-prev]');
    const next = section.querySelector('[data-release-next]');
    const status = section.querySelector('[data-release-status]');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let track;
    let request;
    let cleanupTrack = () => {};

    const bindTrack = () => {
        cleanupTrack();
        track = section.querySelector('[data-release-track]');
        previous.hidden = next.hidden = !track;
        if (!track) return;
        const update = () => {
            previous.hidden = next.hidden = track.scrollWidth <= track.clientWidth + 2;
            previous.disabled = track.scrollLeft <= 2;
            next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;
            const cards = [...track.querySelectorAll('[data-release-card]')];
            const trackRect = track.getBoundingClientRect();
            const center = trackRect.left + trackRect.width / 2;
            const closest = cards.reduce((best, card) => {
                const rect = card.getBoundingClientRect();
                const distance = Math.abs(rect.left + rect.width / 2 - center);
                return !best || distance < best.distance ? { card, distance } : best;
            }, null)?.card;
            cards.forEach((card) => card.classList.toggle('is-centered', innerWidth > 700 && card === closest));
        };
        let frame;
        const onScroll = () => { cancelAnimationFrame(frame); frame = requestAnimationFrame(update); };
        const onKey = (event) => {
            if (event.target !== track || innerWidth <= 700 || !['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
            event.preventDefault();
            move(event.key === 'ArrowLeft' ? -1 : 1);
        };
        track.addEventListener('scroll', onScroll, { passive: true });
        track.addEventListener('keydown', onKey);
        const resize = new ResizeObserver(update);
        resize.observe(track);
        update();
        const boundTrack = track;
        cleanupTrack = () => {
            cancelAnimationFrame(frame);
            boundTrack.removeEventListener('scroll', onScroll);
            boundTrack.removeEventListener('keydown', onKey);
            resize.disconnect();
        };
    };
    const move = (direction) => {
        const card = track?.querySelector('[data-release-card]');
        if (card) track.scrollBy({ left: direction * (card.getBoundingClientRect().width + 24), behavior: reducedMotion.matches ? 'instant' : 'smooth' });
    };
    previous.addEventListener('click', () => move(-1));
    next.addEventListener('click', () => move(1));
    bindTrack();

    const loadFilter = async (url, pushHistory = true) => {
        request?.abort();
        const controller = new AbortController();
        request = controller;
        section.querySelector('[data-release-results]').setAttribute('aria-busy', 'true');
        status.textContent = 'Loading releases…';
        status.classList.add('home-sr-only');
        try {
            const response = await fetch(url, { signal: controller.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) throw new Error('Unable to load releases');
            const document = new DOMParser().parseFromString(await response.text(), 'text/html');
            const results = document.querySelector('[data-release-results]');
            const newFilters = document.querySelector('[data-release-filters]');
            if (!results || !newFilters) throw new Error('Invalid releases response');
            // Do not detach elements that still belong to active animation contexts.
            page.dispatchEvent(new Event('releases:before-update'));
            section.querySelector('[data-release-results]').replaceWith(results);
            const selected = newFilters.querySelector('[aria-current="true"]')?.dataset.releaseFilter;
            filters.querySelectorAll('a').forEach((link) => {
                if (link.dataset.releaseFilter === selected) link.setAttribute('aria-current', 'true');
                else link.removeAttribute('aria-current');
            });
            section.querySelector('[data-releases-all]').href = document.querySelector('[data-releases-all]').href;
            if (pushHistory) history.pushState({ releaseFilter: selected }, '', url);
            bindTrack();
            status.textContent = results.querySelector('[data-release-count]').textContent;
            page.dispatchEvent(new Event('releases:updated'));
        } catch (error) {
            if (error.name === 'AbortError') return;
            status.textContent = 'Releases could not be loaded. Select the fandom again to retry, or use View All.';
            status.classList.remove('home-sr-only');
        } finally {
            if (request === controller) section.querySelector('[data-release-results]').removeAttribute('aria-busy');
        }
    };
    filters.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-release-filter]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button !== 0) return;
        event.preventDefault();
        loadFilter(link.href);
    });
    window.addEventListener('popstate', () => loadFilter(location.href, false));
}
