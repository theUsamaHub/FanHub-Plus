import Swiper from 'swiper';
import { Navigation, Pagination, A11y, Keyboard } from 'swiper/modules';
import 'swiper/css';
import 'swiper/css/pagination';

export function initReleaseTimeline(page) {
    const section = page.querySelector('[data-upcoming-section]');
    if (!section) return;
    const filters = section.querySelector('[data-release-filters]');
    const previous = section.querySelector('[data-release-prev]');
    const next = section.querySelector('[data-release-next]');
    const pagination = section.querySelector('[data-release-pagination]');
    const status = section.querySelector('[data-release-status]');
    const reducedMotion = matchMedia('(prefers-reduced-motion: reduce)');
    let swiper;
    let request;
    let resizeObserver;

    const mount = () => {
        resizeObserver?.disconnect();
        resizeObserver = null;
        swiper?.destroy(true, true);
        swiper = null;
        const container = section.querySelector('[data-release-swiper]');
        previous.hidden = next.hidden = !container;
        if (!container) {
            pagination.replaceChildren();
            return;
        }
        swiper = new Swiper(container, {
            modules: [Navigation, Pagination, A11y, Keyboard],
            slidesPerView: 'auto',
            spaceBetween: 24,
            speed: reducedMotion.matches ? 0 : 450,
            watchOverflow: true,
            navigation: { prevEl: previous, nextEl: next },
            pagination: { el: pagination, clickable: true },
            keyboard: { enabled: true, onlyInViewport: true },
            a11y: {
                containerMessage: 'Upcoming releases. Swipe, or use the arrow buttons and arrow keys to browse.',
                prevSlideMessage: 'Previous releases',
                nextSlideMessage: 'Next releases',
            },
        });
        container.addEventListener('keydown', (event) => {
            if (!['ArrowLeft', 'ArrowRight'].includes(event.key) || event.target.closest('button, a')) return;
            event.preventDefault();
            if (event.key === 'ArrowRight') swiper.slideNext();
            else swiper.slidePrev();
        });
        const updateCentered = () => {
            const cards = [...container.querySelectorAll('[data-release-card]')];
            const rect = container.getBoundingClientRect();
            const center = rect.left + rect.width / 2;
            let best = null;
            cards.forEach((card) => {
                const box = card.getBoundingClientRect();
                const distance = Math.abs(box.left + box.width / 2 - center);
                if (!best || distance < best.distance) best = { card, distance };
            });
            cards.forEach((card) => card.classList.toggle('is-centered', innerWidth > 700 && best?.card === card));
        };
        swiper.on('slideChange', updateCentered);
        updateCentered();
        resizeObserver = new ResizeObserver(updateCentered);
        resizeObserver.observe(container);
    };
    mount();

    reducedMotion.addEventListener('change', () => {
        if (swiper) swiper.params.speed = reducedMotion.matches ? 0 : 450;
    });

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
            mount();
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
        const url = new URL(location.href);
        url.searchParams.set('release_category', link.dataset.releaseFilter);
        url.hash = new URL(link.href).hash;
        loadFilter(url);
    });
    window.addEventListener('popstate', () => loadFilter(location.href, false));
}
